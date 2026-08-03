<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncomingLetterRequest;
use App\Http\Requests\UpdateIncomingLetterRequest;
use App\Models\Division;
use App\Models\IncomingLetter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class IncomingLetterController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:'.IncomingLetter::STATUS_BARU_DITERIMA.','.IncomingLetter::STATUS_MENUNGGU_PEMERIKSAAN],
            'priority' => ['nullable', 'string', 'max:50'],
            'destination_division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'received_date' => ['nullable', 'date'],
        ]);

        $incomingLetters = IncomingLetter::query()
            ->with([
                'creator:id,name',
                'destinationDivision:id,name,code',
            ])
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('agenda_number', 'like', "%{$search}%")
                        ->orWhere('letter_number', 'like', "%{$search}%")
                        ->orWhere('sender_name', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['priority'] ?? null, fn (Builder $query, string $priority) => $query->where('priority', $priority))
            ->when(
                $filters['destination_division_id'] ?? null,
                fn (Builder $query, int $divisionId) => $query->where('destination_division_id', $divisionId),
            )
            ->when(
                $filters['received_date'] ?? null,
                fn (Builder $query, string $receivedDate) => $query->whereDate('received_date', $receivedDate),
            )
            ->orderByDesc('received_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json($incomingLetters);
        }

        return view('incoming-letters.index', [
            'incomingLetters' => $incomingLetters,
            'divisions' => Division::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'divisions' => Division::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'code']),
            ]);
        }

        return view('incoming-letters.form');
    }

    public function store(StoreIncomingLetterRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $document = $data['document'];
        unset($data['document']);

        $documentPath = $this->storeDocument($document, $data['received_date']);

        try {
            $letter = DB::transaction(function () use ($data, $document, $documentPath, $request) {
                return IncomingLetter::query()->create(array_merge(
                    $data,
                    $this->documentMetadata($document, $documentPath),
                    [
                        'status' => IncomingLetter::STATUS_BARU_DITERIMA,
                        'created_by' => $request->user()->id,
                    ],
                ));
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($documentPath);

            throw $exception;
        }

        $letter->load($this->relations());

        if ($request->expectsJson()) {
            return response()->json($letter, 201);
        }

        return redirect()
            ->route('incoming-letters.show', $letter)
            ->with('success', 'Surat masuk berhasil ditambahkan.');
    }

    public function show(Request $request, IncomingLetter $incomingLetter): View|JsonResponse
    {
        $incomingLetter->load($this->relations());

        if ($request->expectsJson()) {
            return response()->json($incomingLetter);
        }

        return view('incoming-letters.show', compact('incomingLetter'));
    }

    public function edit(Request $request, IncomingLetter $incomingLetter): View|JsonResponse
    {
        $incomingLetter->load($this->relations());

        if ($request->expectsJson()) {
            return response()->json([
                'incoming_letter' => $incomingLetter,
                'divisions' => Division::query()
                    ->where(function (Builder $query) use ($incomingLetter) {
                        $query->where('is_active', true);

                        if ($incomingLetter->destination_division_id !== null) {
                            $query->orWhere('id', $incomingLetter->destination_division_id);
                        }
                    })
                    ->orderBy('name')
                    ->get(['id', 'name', 'code', 'is_active']),
            ]);
        }

        return view('incoming-letters.form', compact('incomingLetter'));
    }

    public function update(
        UpdateIncomingLetterRequest $request,
        IncomingLetter $incomingLetter,
    ): JsonResponse|RedirectResponse {
        $data = $request->validated();
        $document = $data['document'] ?? null;
        unset($data['document']);

        $previousDocumentPath = $incomingLetter->document_path;
        $documentPath = null;
        $documentData = [];

        if ($document !== null) {
            $documentPath = $this->storeDocument($document, $data['received_date']);
            $documentData = $this->documentMetadata($document, $documentPath);
        }

        try {
            $letter = DB::transaction(function () use ($data, $documentData, $incomingLetter) {
                $incomingLetter->update(array_merge(
                    $data,
                    $documentData,
                ));

                return $incomingLetter;
            });
        } catch (\Throwable $exception) {
            if ($documentPath !== null) {
                Storage::disk('local')->delete($documentPath);
            }

            throw $exception;
        }

        if ($documentPath !== null) {
            Storage::disk('local')->delete($previousDocumentPath);
        }

        $letter->refresh()->load($this->relations());

        if ($request->expectsJson()) {
            return response()->json($letter);
        }

        return redirect()
            ->route('incoming-letters.show', $letter)
            ->with('success', 'Surat masuk berhasil diperbarui.');
    }

    public function submitForReview(
        Request $request,
        IncomingLetter $incomingLetter,
    ): JsonResponse|RedirectResponse {
        $submittedAt = now();

        $updated = IncomingLetter::query()
            ->whereKey($incomingLetter)
            ->where('status', IncomingLetter::STATUS_BARU_DITERIMA)
            ->update([
                'status' => IncomingLetter::STATUS_MENUNGGU_PEMERIKSAAN,
                'submitted_for_review_at' => $submittedAt,
            ]);

        abort_unless($updated === 1, 422, 'Surat masuk tidak dapat diajukan untuk pemeriksaan.');

        $incomingLetter->refresh()->load($this->relations());

        if ($request->expectsJson()) {
            return response()->json($incomingLetter);
        }

        return redirect()
            ->route('incoming-letters.show', $incomingLetter)
            ->with('success', 'Surat masuk berhasil dikirim untuk pemeriksaan.');
    }

    public function preview(IncomingLetter $incomingLetter): BinaryFileResponse
    {
        $response = response()->file($this->documentAbsolutePath($incomingLetter), [
            'Content-Type' => $incomingLetter->document_mime_type,
        ]);

        return $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $incomingLetter->original_document_name,
            $this->asciiFileName($incomingLetter->original_document_name),
        );
    }

    public function download(IncomingLetter $incomingLetter): BinaryFileResponse
    {
        return response()->download(
            $this->documentAbsolutePath($incomingLetter),
            $incomingLetter->original_document_name,
            ['Content-Type' => $incomingLetter->document_mime_type],
        );
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return [
            'creator:id,name',
            'destinationDivision:id,name,code',
        ];
    }

    private function storeDocument(UploadedFile $document, string $receivedDate): string
    {
        $year = Carbon::parse($receivedDate)->year;
        $extension = Str::lower($document->extension());
        $fileName = Str::uuid()->toString().'.'.$extension;
        $path = $document->storeAs("incoming-letters/{$year}", $fileName, 'local');

        if ($path === false) {
            throw new \RuntimeException('Dokumen surat masuk gagal disimpan.');
        }

        return $path;
    }

    /**
     * @return array<string, int|string>
     */
    private function documentMetadata(UploadedFile $document, string $path): array
    {
        return [
            'document_path' => $path,
            'original_document_name' => $document->getClientOriginalName(),
            'document_mime_type' => $document->getMimeType() ?: 'application/octet-stream',
            'document_size' => $document->getSize() ?: 0,
        ];
    }

    private function documentAbsolutePath(IncomingLetter $incomingLetter): string
    {
        abort_unless(Storage::disk('local')->exists($incomingLetter->document_path), 404);

        return Storage::disk('local')->path($incomingLetter->document_path);
    }

    private function asciiFileName(string $fileName): string
    {
        return Str::ascii($fileName) ?: 'document';
    }
}

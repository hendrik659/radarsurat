<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomingLetterReportRequest;
use App\Http\Requests\OutgoingLetterReportRequest;
use App\Models\User;
use App\Services\ReportExcelService;
use App\Services\ReportQueryService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportQueryService $queries,
        private readonly ReportExcelService $excel,
    ) {}

    public function incomingLetters(IncomingLetterReportRequest $request): View
    {
        $filters = $request->validated();
        $user = $request->user();
        $query = $this->queries->incomingQuery($user, $filters);
        $hasGlobalScope = $this->queries->hasGlobalScope($user);

        return view('reports.incoming-letters', [
            'incomingLetters' => (clone $query)
                ->with('destinationDivision:id,name')
                ->orderByDesc('received_date')
                ->latest('id')
                ->paginate(25)
                ->withQueryString(),
            'summary' => $this->queries->incomingSummary($query),
            'recap' => $hasGlobalScope ? $this->queries->incomingRecap($query) : collect(),
            'divisions' => $hasGlobalScope ? $this->queries->activeDivisions() : collect(),
            'filters' => $filters,
            'hasGlobalScope' => $hasGlobalScope,
            'divisionLabel' => $this->queries->divisionLabel($user, $filters),
        ]);
    }

    public function exportIncomingLetters(IncomingLetterReportRequest $request): BinaryFileResponse
    {
        $filters = $request->validated();
        $user = $request->user();
        $query = $this->queries->incomingQuery($user, $filters);
        $summary = $this->queries->incomingSummary($query);
        $path = $this->excel->writeIncoming(
            (clone $query)
                ->with('destinationDivision:id,name')
                ->orderBy('received_date')
                ->orderBy('id')
                ->lazy(500),
            $summary,
            $this->exportMetadata($user, $filters),
        );

        return response()
            ->download(
                $path,
                $this->exportFileName('laporan-surat-masuk', $user, $filters),
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            )
            ->deleteFileAfterSend(true);
    }

    public function outgoingLetters(OutgoingLetterReportRequest $request): View
    {
        $filters = $request->validated();
        $user = $request->user();
        $query = $this->queries->outgoingQuery($user, $filters);
        $hasGlobalScope = $this->queries->hasGlobalScope($user);

        return view('reports.outgoing-letters', [
            'outgoingLetters' => (clone $query)
                ->with(['division:id,name', 'creator:id,name'])
                ->orderByDesc('letter_date')
                ->latest('id')
                ->paginate(25)
                ->withQueryString(),
            'summary' => $this->queries->outgoingSummary($query),
            'recap' => $hasGlobalScope ? $this->queries->outgoingRecap($query) : collect(),
            'divisions' => $hasGlobalScope ? $this->queries->activeDivisions() : collect(),
            'filters' => $filters,
            'hasGlobalScope' => $hasGlobalScope,
            'divisionLabel' => $this->queries->divisionLabel($user, $filters),
        ]);
    }

    public function exportOutgoingLetters(OutgoingLetterReportRequest $request): BinaryFileResponse
    {
        $filters = $request->validated();
        $user = $request->user();
        $query = $this->queries->outgoingQuery($user, $filters);
        $summary = $this->queries->outgoingSummary($query);
        $path = $this->excel->writeOutgoing(
            (clone $query)
                ->with(['division:id,name', 'creator:id,name'])
                ->orderBy('letter_date')
                ->orderBy('id')
                ->lazy(500),
            $summary,
            $this->exportMetadata($user, $filters),
        );

        return response()
            ->download(
                $path,
                $this->exportFileName('laporan-surat-keluar', $user, $filters),
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            )
            ->deleteFileAfterSend(true);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{period: string, division: string, exported_by: string, exported_at: string}
     */
    private function exportMetadata(User $user, array $filters): array
    {
        return [
            'period' => $this->queries->periodLabel($filters),
            'division' => $this->queries->divisionLabel($user, $filters),
            'exported_by' => $user->name,
            'exported_at' => now()->format('d-m-Y H:i:s'),
        ];
    }

    /** @param array<string, mixed> $filters */
    private function exportFileName(string $baseName, User $user, array $filters): string
    {
        $parts = [$baseName];

        if (! $this->queries->hasGlobalScope($user)) {
            $parts[] = Str::slug($user->division?->name ?? 'divisi');
        }

        $parts[] = $this->periodFileName($filters);

        return implode('-', $parts).'.xlsx';
    }

    /** @param array<string, mixed> $filters */
    private function periodFileName(array $filters): string
    {
        $start = filled($filters['start_date'] ?? null)
            ? CarbonImmutable::parse($filters['start_date'])
            : null;
        $end = filled($filters['end_date'] ?? null)
            ? CarbonImmutable::parse($filters['end_date'])
            : null;

        if ($start && $end && $start->isSameMonth($end)) {
            return $start->format('Y-m');
        }

        if ($start && $end) {
            return $start->format('Y-m-d').'-sd-'.$end->format('Y-m-d');
        }

        if ($start) {
            return 'mulai-'.$start->format('Y-m-d');
        }

        if ($end) {
            return 'sampai-'.$end->format('Y-m-d');
        }

        return now()->format('Y-m');
    }
}

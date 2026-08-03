<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncomingLetterAssignmentRequest;
use App\Models\Division;
use App\Models\IncomingLetter;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class IncomingLetterAssignmentController extends Controller
{
    public function create(IncomingLetter $incomingLetter): View
    {
        Gate::authorize('assign', $incomingLetter);
        $this->ensureAssignable($incomingLetter);

        $incomingLetter->load([
            'creator:id,name',
            'review.reviewer:id,name',
            'destinationDivision:id,name,code,is_active',
            'assignment.assignee:id,name',
        ]);

        return view('incoming-letters.assignment', [
            'incomingLetter' => $incomingLetter,
            'members' => $this->eligibleMembers($incomingLetter),
        ]);
    }

    public function store(
        StoreIncomingLetterAssignmentRequest $request,
        IncomingLetter $incomingLetter,
    ): RedirectResponse {
        Gate::authorize('assign', $incomingLetter);

        $data = $request->validated();
        $assignerId = $request->user()->id;

        $assignedLetter = DB::transaction(function () use ($data, $incomingLetter, $assignerId) {
            $lockedLetter = IncomingLetter::query()
                ->lockForUpdate()
                ->findOrFail($incomingLetter->id);

            Gate::authorize('assign', $lockedLetter);
            $this->ensureAssignable($lockedLetter);

            $destinationDivision = Division::query()
                ->whereKey($lockedLetter->destination_division_id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            abort_unless(
                $destinationDivision !== null,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'Divisi tujuan tidak tersedia atau sudah tidak aktif.',
            );

            $assignee = User::query()
                ->with('role:id,slug')
                ->lockForUpdate()
                ->find($data['assigned_to']);

            $this->ensureEligibleAssignee($assignee, $destinationDivision->id);

            $instruction = $data['instruction'] ?? null;

            $lockedLetter->assignment()->create([
                'assigned_by' => $assignerId,
                'assigned_to' => $assignee->id,
                'division_id' => $destinationDivision->id,
                'instruction' => $instruction,
                'due_date' => $data['due_date'] ?? null,
                'assigned_at' => now(),
            ]);

            $lockedLetter->update([
                'status' => IncomingLetter::STATUS_DITUGASKAN_KE_ANGGOTA,
            ]);

            $lockedLetter->statusHistories()->create([
                'previous_status' => IncomingLetter::STATUS_DITERUSKAN_KE_DIVISI,
                'new_status' => IncomingLetter::STATUS_DITUGASKAN_KE_ANGGOTA,
                'activity' => "Surat ditugaskan kepada {$assignee->name}",
                'notes' => $instruction,
                'changed_by' => $assignerId,
            ]);

            return $lockedLetter;
        });

        return redirect()
            ->route('incoming-letters.show', $assignedLetter)
            ->with('success', 'Surat masuk berhasil ditugaskan kepada anggota divisi.');
    }

    /**
     * @return Collection<int, User>
     */
    private function eligibleMembers(IncomingLetter $incomingLetter): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->where('division_id', $incomingLetter->destination_division_id)
            ->whereHas('role', fn ($query) => $query->where('slug', 'anggota_divisi'))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'position', 'division_id']);
    }

    private function ensureAssignable(IncomingLetter $incomingLetter): void
    {
        abort_unless(
            $incomingLetter->status === IncomingLetter::STATUS_DITERUSKAN_KE_DIVISI,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Surat masuk tidak berada pada status diteruskan ke divisi.',
        );

        abort_unless(
            $incomingLetter->review()->exists(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Surat masuk belum memiliki hasil pemeriksaan.',
        );

        abort_unless(
            $incomingLetter->destination_division_id !== null,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Surat masuk belum memiliki divisi tujuan.',
        );

        abort_if(
            $incomingLetter->assignment()->exists(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Surat masuk sudah memiliki penugasan.',
        );
    }

    private function ensureEligibleAssignee(?User $assignee, int $divisionId): void
    {
        abort_unless(
            $assignee !== null,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Anggota yang dipilih tidak ditemukan.',
        );

        abort_unless(
            $assignee->is_active,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Anggota yang dipilih harus memiliki akun aktif.',
        );

        abort_unless(
            $assignee->role?->slug === 'anggota_divisi',
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Pengguna yang dipilih harus memiliki role Anggota Divisi.',
        );

        abort_unless(
            $assignee->division_id === $divisionId,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Anggota yang dipilih harus berasal dari divisi tujuan surat.',
        );
    }
}

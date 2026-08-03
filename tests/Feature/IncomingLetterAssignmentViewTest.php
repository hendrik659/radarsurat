<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\IncomingLetter;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomingLetterAssignmentViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_destination_division_head_sees_the_assignment_button(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division, true, 'Ketua Redaksi');
        $letter = $this->makeLetter($divisionHead, $division);

        $this->actingAs($divisionHead)
            ->get(route('incoming-letters.show', $letter))
            ->assertOk()
            ->assertSee('data-testid="incoming-letter-assignment-link"', false)
            ->assertSee('href="'.route('incoming-letters.assignment.create', $letter).'"', false)
            ->assertSee('Tugaskan Anggota');
    }

    public function test_unauthorized_users_do_not_see_the_assignment_button(): void
    {
        $destinationDivision = $this->makeDivision();
        $otherDivision = $this->makeDivision('Keuangan', 'KEU');
        $creator = $this->makeUser('admin_surat', null, true, 'Pembuat Surat');
        $letter = $this->makeLetter($creator, $destinationDivision);
        $users = [
            $this->makeUser('ketua_divisi', $otherDivision, true, 'Ketua Divisi Lain'),
            $this->makeUser('pimpinan', null, true, 'Pimpinan'),
            $this->makeUser('admin_surat', null, true, 'Admin Surat'),
            $this->makeUser('anggota_divisi', $destinationDivision, true, 'Anggota Divisi'),
        ];

        foreach ($users as $user) {
            $this->actingAs($user)
                ->get(route('incoming-letters.show', $letter))
                ->assertOk()
                ->assertDontSee('data-testid="incoming-letter-assignment-link"', false);
        }
    }

    public function test_assignment_button_only_appears_for_an_unassigned_forwarded_letter(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division, true, 'Ketua Redaksi');
        $waitingLetter = $this->makeLetter(
            $divisionHead,
            $division,
            IncomingLetter::STATUS_MENUNGGU_PEMERIKSAAN,
            false,
        );
        $assignedLetter = $this->makeLetter(
            $divisionHead,
            $division,
            IncomingLetter::STATUS_DITUGASKAN_KE_ANGGOTA,
        );
        $member = $this->makeUser('anggota_divisi', $division, true, 'Anggota Redaksi');
        $assignedLetter->assignment()->create([
            'assigned_by' => $divisionHead->id,
            'assigned_to' => $member->id,
            'division_id' => $division->id,
            'instruction' => null,
            'due_date' => null,
            'assigned_at' => now(),
        ]);

        foreach ([$waitingLetter, $assignedLetter] as $letter) {
            $this->actingAs($divisionHead)
                ->get(route('incoming-letters.show', $letter))
                ->assertOk()
                ->assertDontSee('data-testid="incoming-letter-assignment-link"', false);
        }
    }

    public function test_destination_division_head_can_open_assignment_form_with_eligible_members_only(): void
    {
        $division = $this->makeDivision();
        $otherDivision = $this->makeDivision('Pemasaran', 'PEM');
        $divisionHead = $this->makeUser('ketua_divisi', $division, true, 'Ketua Redaksi');
        $activeMember = $this->makeUser('anggota_divisi', $division, true, 'Ani Anggota Aktif');
        $inactiveMember = $this->makeUser('anggota_divisi', $division, false, 'Budi Anggota Nonaktif');
        $otherMember = $this->makeUser('anggota_divisi', $otherDivision, true, 'Citra Divisi Lain');
        $this->makeUser('ketua_divisi', $division, true, 'Dedi Bukan Anggota');
        $letter = $this->makeLetter($divisionHead, $division, reviewNote: 'Mohon segera ditugaskan.');

        $this->actingAs($divisionHead)
            ->get(route('incoming-letters.assignment.create', $letter))
            ->assertOk()
            ->assertViewIs('incoming-letters.assignment')
            ->assertViewHas('incomingLetter', fn (IncomingLetter $viewLetter) => $viewLetter->is($letter))
            ->assertViewHas('members', fn ($members) => $members->pluck('id')->all() === [$activeMember->id])
            ->assertSee('Tugaskan Surat kepada Anggota')
            ->assertSee('action="'.route('incoming-letters.assignment.store', $letter).'"', false)
            ->assertSee('data="'.route('incoming-letters.preview', $letter).'"', false)
            ->assertSee('data-testid="incoming-letter-assignment-form"', false)
            ->assertSee('<option value="'.$activeMember->id.'"', false)
            ->assertDontSee($inactiveMember->name)
            ->assertDontSee($otherMember->name)
            ->assertSee('name="instruction"', false)
            ->assertSee('maxlength="2000"', false)
            ->assertSee('name="due_date"', false)
            ->assertSee('Mohon segera ditugaskan.')
            ->assertSee('Tugaskan surat ini kepada anggota yang dipilih? Setelah disimpan, anggota tersebut menjadi penanggung jawab surat.')
            ->assertDontSee('summary')
            ->assertDontSee($letter->document_path)
            ->assertDontSee('/storage/');
    }

    public function test_assignment_result_and_history_are_displayed_on_detail(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division, true, 'Ketua Redaksi');
        $member = $this->makeUser('anggota_divisi', $division, true, 'Anggota Penanggung Jawab');
        $letter = $this->makeLetter(
            $divisionHead,
            $division,
            IncomingLetter::STATUS_DITUGASKAN_KE_ANGGOTA,
        );
        $letter->assignment()->create([
            'assigned_by' => $divisionHead->id,
            'assigned_to' => $member->id,
            'division_id' => $division->id,
            'instruction' => 'Koordinasikan jawaban dengan tim redaksi.',
            'due_date' => '2026-08-10',
            'assigned_at' => '2026-08-03 16:30:00',
        ]);
        $letter->statusHistories()->create([
            'previous_status' => IncomingLetter::STATUS_DITERUSKAN_KE_DIVISI,
            'new_status' => IncomingLetter::STATUS_DITUGASKAN_KE_ANGGOTA,
            'activity' => 'Surat ditugaskan kepada Anggota Penanggung Jawab',
            'notes' => 'Koordinasikan jawaban dengan tim redaksi.',
            'changed_by' => $divisionHead->id,
        ]);

        $this->actingAs($member)
            ->get(route('incoming-letters.show', $letter))
            ->assertOk()
            ->assertSee('Hasil Penugasan')
            ->assertSee('Ditugaskan oleh')
            ->assertSee($divisionHead->name)
            ->assertSee('Ditugaskan kepada')
            ->assertSee($member->name)
            ->assertSee($division->name)
            ->assertSee('03-08-2026 16:30')
            ->assertSee('10-08-2026')
            ->assertSee('Koordinasikan jawaban dengan tim redaksi.')
            ->assertSee('Surat ditugaskan kepada Anggota Penanggung Jawab')
            ->assertSee('Diteruskan ke Divisi')
            ->assertSee('Ditugaskan ke Anggota')
            ->assertSee('Diubah oleh '.$divisionHead->name)
            ->assertDontSee('data-testid="incoming-letter-assignment-link"', false);
    }

    public function test_empty_instruction_and_due_date_use_the_required_fallbacks(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $member = $this->makeUser('anggota_divisi', $division);
        $letter = $this->makeLetter(
            $divisionHead,
            $division,
            IncomingLetter::STATUS_DITUGASKAN_KE_ANGGOTA,
        );
        $letter->assignment()->create([
            'assigned_by' => $divisionHead->id,
            'assigned_to' => $member->id,
            'division_id' => $division->id,
            'instruction' => null,
            'due_date' => null,
            'assigned_at' => now(),
        ]);

        $this->actingAs($member)
            ->get(route('incoming-letters.show', $letter))
            ->assertOk()
            ->assertSee('Tidak ada instruksi.')
            ->assertSee('Tidak ditentukan.');
    }

    public function test_index_displays_assigned_status_badge_and_assignee_name(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $member = $this->makeUser('anggota_divisi', $division, true, 'Anggota Index');
        $letter = $this->makeLetter(
            $divisionHead,
            $division,
            IncomingLetter::STATUS_DITUGASKAN_KE_ANGGOTA,
        );
        $letter->assignment()->create([
            'assigned_by' => $divisionHead->id,
            'assigned_to' => $member->id,
            'division_id' => $division->id,
            'instruction' => null,
            'due_date' => null,
            'assigned_at' => now(),
        ]);

        $this->actingAs($member)
            ->get(route('incoming-letters.index', [
                'status' => IncomingLetter::STATUS_DITUGASKAN_KE_ANGGOTA,
            ]))
            ->assertOk()
            ->assertSee('<option value="ditugaskan_ke_anggota" selected>Ditugaskan ke Anggota</option>', false)
            ->assertSee('Penanggung Jawab')
            ->assertSee('text-bg-primary', false)
            ->assertSee('Ditugaskan ke Anggota')
            ->assertSee($member->name);
    }

    private function makeDivision(
        string $name = 'Redaksi',
        string $code = 'RED',
        bool $isActive = true,
    ): Division {
        return Division::query()->create([
            'name' => $name,
            'code' => $code,
            'is_active' => $isActive,
        ]);
    }

    private function makeUser(
        string $roleSlug,
        ?Division $division = null,
        bool $isActive = true,
        ?string $name = null,
    ): User {
        $role = Role::query()->firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => str($roleSlug)->replace('_', ' ')->title()->toString()],
        );

        return User::query()->create([
            'name' => $name ?? fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role_id' => $role->id,
            'division_id' => $division?->id,
            'is_active' => $isActive,
        ]);
    }

    private function makeLetter(
        User $creator,
        ?Division $destinationDivision,
        string $status = IncomingLetter::STATUS_DITERUSKAN_KE_DIVISI,
        bool $withReview = true,
        ?string $reviewNote = null,
    ): IncomingLetter {
        $letter = IncomingLetter::query()->create([
            'agenda_number' => 'AGD-'.fake()->unique()->numerify('####'),
            'letter_number' => '001/RS/VIII/2026',
            'sender_name' => 'Instansi Pengirim',
            'addressed_to' => 'Radar Surat',
            'letter_date' => '2026-08-01',
            'received_date' => '2026-08-03',
            'received_via' => 'fisik',
            'subject' => 'Surat untuk penugasan anggota',
            'priority' => 'biasa',
            'destination_division_id' => $destinationDivision?->id,
            'document_path' => 'incoming-letters/2026/private-document.pdf',
            'original_document_name' => 'surat.pdf',
            'document_mime_type' => 'application/pdf',
            'document_size' => 1024,
            'status' => $status,
            'created_by' => $creator->id,
            'submitted_for_review_at' => now(),
        ]);

        if ($withReview && $destinationDivision) {
            $letter->review()->create([
                'reviewed_by' => $creator->id,
                'destination_division_id' => $destinationDivision->id,
                'review_note' => $reviewNote,
                'reviewed_at' => now(),
            ]);
        }

        return $letter;
    }
}

<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\IncomingLetter;
use App\Models\IncomingLetterStatusHistory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class IncomingLetterAssignmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_assignment_routes(): void
    {
        $division = $this->makeDivision();
        $letter = $this->makeLetter($this->makeUser('ketua_divisi', $division), $division);

        $this->get(route('incoming-letters.assignment.create', $letter))
            ->assertRedirect(route('login'));
        $this->post(route('incoming-letters.assignment.store', $letter), [])
            ->assertRedirect(route('login'));
    }

    public function test_destination_division_head_can_open_and_store_an_assignment(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division, true, 'Ketua Redaksi');
        $firstMember = $this->makeUser('anggota_divisi', $division, true, 'Ani Anggota');
        $secondMember = $this->makeUser('anggota_divisi', $division, true, 'Zaki Anggota');
        $this->makeUser('anggota_divisi', $division, false, 'Anggota Nonaktif');
        $otherDivision = $this->makeDivision('Pemasaran', 'PEM');
        $this->makeUser('anggota_divisi', $otherDivision, true, 'Anggota Divisi Lain');
        $this->makeUser('ketua_divisi', $division, true, 'Bukan Anggota');
        $letter = $this->makeLetter($divisionHead, $division);

        $this->actingAs($divisionHead)
            ->get(route('incoming-letters.assignment.create', $letter))
            ->assertOk()
            ->assertViewIs('incoming-letters.assignment')
            ->assertViewHas('incomingLetter', fn (IncomingLetter $viewLetter) => $viewLetter->is($letter))
            ->assertViewHas('members', fn ($members) => $members->pluck('id')->all() === [
                $firstMember->id,
                $secondMember->id,
            ]);

        $this->actingAs($divisionHead)
            ->post(route('incoming-letters.assignment.store', $letter), [
                'assigned_to' => $firstMember->id,
                'instruction' => 'Pelajari dan siapkan tindak lanjut.',
                'due_date' => today()->addDays(3)->toDateString(),
            ])
            ->assertRedirect(route('incoming-letters.show', $letter))
            ->assertSessionHas('success');

        $assignment = $letter->assignment()->firstOrFail();

        $this->assertSame($divisionHead->id, $assignment->assigned_by);
        $this->assertSame($firstMember->id, $assignment->assigned_to);
        $this->assertSame($division->id, $assignment->division_id);
        $this->assertSame('Pelajari dan siapkan tindak lanjut.', $assignment->instruction);
        $this->assertSame(today()->addDays(3)->toDateString(), $assignment->due_date->toDateString());
        $this->assertNotNull($assignment->assigned_at);
        $this->assertTrue($assignment->assigner->is($divisionHead));
        $this->assertTrue($assignment->assignee->is($firstMember));
        $this->assertTrue($assignment->division->is($division));

        $letter->refresh();

        $this->assertSame(IncomingLetter::STATUS_DITUGASKAN_KE_ANGGOTA, $letter->status);
        $this->assertDatabaseHas('incoming_letter_status_histories', [
            'incoming_letter_id' => $letter->id,
            'previous_status' => IncomingLetter::STATUS_DITERUSKAN_KE_DIVISI,
            'new_status' => IncomingLetter::STATUS_DITUGASKAN_KE_ANGGOTA,
            'activity' => 'Surat ditugaskan kepada Ani Anggota',
            'notes' => 'Pelajari dan siapkan tindak lanjut.',
            'changed_by' => $divisionHead->id,
        ]);
    }

    public function test_another_division_head_is_forbidden(): void
    {
        $destinationDivision = $this->makeDivision();
        $otherDivision = $this->makeDivision('Pemasaran', 'PEM');
        $creator = $this->makeUser('ketua_divisi', $destinationDivision);
        $otherDivisionHead = $this->makeUser('ketua_divisi', $otherDivision);
        $letter = $this->makeLetter($creator, $destinationDivision);

        $this->actingAs($otherDivisionHead)
            ->get(route('incoming-letters.assignment.create', $letter))
            ->assertForbidden();
    }

    public function test_non_division_head_roles_are_forbidden(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $letter = $this->makeLetter($divisionHead, $division);
        $users = [
            $this->makeUser('pimpinan', $division),
            $this->makeUser('admin_surat', $division),
            $this->makeUser('anggota_divisi', $division),
        ];

        foreach ($users as $user) {
            $this->actingAs($user)
                ->get(route('incoming-letters.assignment.create', $letter))
                ->assertForbidden();
        }
    }

    public function test_inactive_division_head_is_rejected_by_active_middleware(): void
    {
        $division = $this->makeDivision();
        $inactiveDivisionHead = $this->makeUser('ketua_divisi', $division, false);
        $letter = $this->makeLetter($inactiveDivisionHead, $division);

        $this->actingAs($inactiveDivisionHead)
            ->get(route('incoming-letters.assignment.create', $letter))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_letter_with_another_status_is_forbidden(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $letter = $this->makeLetter(
            $divisionHead,
            $division,
            IncomingLetter::STATUS_MENUNGGU_PEMERIKSAAN,
        );

        $this->actingAs($divisionHead)
            ->get(route('incoming-letters.assignment.create', $letter))
            ->assertForbidden();
    }

    public function test_letter_without_a_review_is_forbidden(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $letter = $this->makeLetter($divisionHead, $division, withReview: false);

        $this->actingAs($divisionHead)
            ->get(route('incoming-letters.assignment.create', $letter))
            ->assertForbidden();
    }

    public function test_letter_without_a_destination_division_is_forbidden(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $letter = $this->makeLetter($divisionHead, null, withReview: false);

        $this->actingAs($divisionHead)
            ->get(route('incoming-letters.assignment.create', $letter))
            ->assertForbidden();
    }

    public function test_assignee_is_required(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $letter = $this->makeLetter($divisionHead, $division);

        $this->actingAs($divisionHead)
            ->post(route('incoming-letters.assignment.store', $letter), [])
            ->assertSessionHasErrors('assigned_to');

        $this->assertAssignmentDidNotChangeLetter($letter);
    }

    public function test_inactive_member_is_rejected(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $inactiveMember = $this->makeUser('anggota_divisi', $division, false);
        $letter = $this->makeLetter($divisionHead, $division);

        $this->actingAs($divisionHead)
            ->post(route('incoming-letters.assignment.store', $letter), [
                'assigned_to' => $inactiveMember->id,
            ])
            ->assertSessionHasErrors('assigned_to');

        $this->assertAssignmentDidNotChangeLetter($letter);
    }

    public function test_user_without_member_role_is_rejected(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $nonMember = $this->makeUser('pimpinan', $division);
        $letter = $this->makeLetter($divisionHead, $division);

        $this->actingAs($divisionHead)
            ->post(route('incoming-letters.assignment.store', $letter), [
                'assigned_to' => $nonMember->id,
            ])
            ->assertSessionHasErrors('assigned_to');

        $this->assertAssignmentDidNotChangeLetter($letter);
    }

    public function test_member_from_another_division_is_rejected(): void
    {
        $division = $this->makeDivision();
        $otherDivision = $this->makeDivision('Pemasaran', 'PEM');
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $otherMember = $this->makeUser('anggota_divisi', $otherDivision);
        $letter = $this->makeLetter($divisionHead, $division);

        $this->actingAs($divisionHead)
            ->post(route('incoming-letters.assignment.store', $letter), [
                'assigned_to' => $otherMember->id,
            ])
            ->assertSessionHasErrors('assigned_to');

        $this->assertAssignmentDidNotChangeLetter($letter);
    }

    public function test_instruction_and_due_date_may_be_empty(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $member = $this->makeUser('anggota_divisi', $division);
        $letter = $this->makeLetter($divisionHead, $division);

        $this->actingAs($divisionHead)
            ->post(route('incoming-letters.assignment.store', $letter), [
                'assigned_to' => $member->id,
                'instruction' => '',
                'due_date' => '',
            ])
            ->assertRedirect(route('incoming-letters.show', $letter));

        $this->assertDatabaseHas('incoming_letter_assignments', [
            'incoming_letter_id' => $letter->id,
            'assigned_to' => $member->id,
            'instruction' => null,
            'due_date' => null,
        ]);
    }

    public function test_past_due_date_is_rejected(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $member = $this->makeUser('anggota_divisi', $division);
        $letter = $this->makeLetter($divisionHead, $division);

        $this->actingAs($divisionHead)
            ->post(route('incoming-letters.assignment.store', $letter), [
                'assigned_to' => $member->id,
                'due_date' => today()->subDay()->toDateString(),
            ])
            ->assertSessionHasErrors('due_date');

        $this->assertAssignmentDidNotChangeLetter($letter);
    }

    public function test_inactive_destination_division_is_rejected_without_partial_data(): void
    {
        $division = $this->makeDivision(isActive: false);
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $member = $this->makeUser('anggota_divisi', $division);
        $letter = $this->makeLetter($divisionHead, $division);

        $this->actingAs($divisionHead)
            ->post(route('incoming-letters.assignment.store', $letter), [
                'assigned_to' => $member->id,
            ])
            ->assertUnprocessable();

        $this->assertAssignmentDidNotChangeLetter($letter);
    }

    public function test_letter_cannot_be_assigned_twice(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $member = $this->makeUser('anggota_divisi', $division);
        $letter = $this->makeLetter($divisionHead, $division);
        $payload = ['assigned_to' => $member->id];

        $this->actingAs($divisionHead)
            ->post(route('incoming-letters.assignment.store', $letter), $payload)
            ->assertRedirect(route('incoming-letters.show', $letter));

        $this->actingAs($divisionHead)
            ->post(route('incoming-letters.assignment.store', $letter), $payload)
            ->assertForbidden();

        $this->assertDatabaseCount('incoming_letter_assignments', 1);
        $this->assertDatabaseCount('incoming_letter_status_histories', 1);
    }

    public function test_assignment_transaction_rolls_back_if_history_cannot_be_created(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $member = $this->makeUser('anggota_divisi', $division);
        $letter = $this->makeLetter($divisionHead, $division);
        IncomingLetterStatusHistory::creating(function () {
            throw new RuntimeException('Simulasi kegagalan history assignment.');
        });
        $exception = null;

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($divisionHead)
                ->post(route('incoming-letters.assignment.store', $letter), [
                    'assigned_to' => $member->id,
                ]);
        } catch (RuntimeException $caughtException) {
            $exception = $caughtException;
        } finally {
            IncomingLetterStatusHistory::flushEventListeners();
        }

        $this->assertNotNull($exception);
        $this->assertSame('Simulasi kegagalan history assignment.', $exception->getMessage());
        $this->assertAssignmentDidNotChangeLetter($letter);
        $this->assertDatabaseCount('incoming_letter_status_histories', 0);
    }

    private function assertAssignmentDidNotChangeLetter(IncomingLetter $letter): void
    {
        $this->assertDatabaseMissing('incoming_letter_assignments', [
            'incoming_letter_id' => $letter->id,
        ]);

        $this->assertSame(
            IncomingLetter::STATUS_DITERUSKAN_KE_DIVISI,
            $letter->fresh()->status,
        );
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
                'review_note' => null,
                'reviewed_at' => now(),
            ]);
        }

        return $letter;
    }
}

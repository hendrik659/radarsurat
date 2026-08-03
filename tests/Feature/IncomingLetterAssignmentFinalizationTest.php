<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\IncomingLetter;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IncomingLetterAssignmentFinalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_only_division_members_see_the_my_tasks_filter(): void
    {
        $division = $this->makeDivision();
        $member = $this->makeUser('anggota_divisi', $division, 'Anggota Redaksi');

        $this->actingAs($member)
            ->get(route('incoming-letters.index'))
            ->assertOk()
            ->assertSee('data-testid="incoming-letter-my-tasks-link"', false)
            ->assertSee('Tugas Saya')
            ->assertDontSee('data-testid="incoming-letter-all-link"', false);

        $this->actingAs($member)
            ->get(route('incoming-letters.index', ['my_tasks' => 1]))
            ->assertOk()
            ->assertSee('btn-primary active', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('data-testid="incoming-letter-all-link"', false)
            ->assertSee('Semua Surat')
            ->assertSee('name="my_tasks" type="hidden" value="1"', false);

        foreach (['ketua_divisi', 'pimpinan', 'admin_surat'] as $roleSlug) {
            $user = $this->makeUser($roleSlug, $roleSlug === 'ketua_divisi' ? $division : null);

            $this->actingAs($user)
                ->get(route('incoming-letters.index'))
                ->assertOk()
                ->assertDontSee('data-testid="incoming-letter-my-tasks-link"', false)
                ->assertDontSee('data-testid="incoming-letter-all-link"', false);
        }
    }

    public function test_my_tasks_only_displays_the_authenticated_members_assignments(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $member = $this->makeUser('anggota_divisi', $division, 'Anggota Pertama');
        $otherMember = $this->makeUser('anggota_divisi', $division, 'Anggota Kedua');
        $ownLetter = $this->makeAssignedLetter($divisionHead, $member, $division, 'AGD-MILIK-SAYA');
        $otherLetter = $this->makeAssignedLetter($divisionHead, $otherMember, $division, 'AGD-MILIK-LAIN');

        $this->actingAs($member)
            ->get(route('incoming-letters.index', ['my_tasks' => 1]))
            ->assertOk()
            ->assertSee($ownLetter->agenda_number)
            ->assertSee($member->name)
            ->assertDontSee($otherLetter->agenda_number)
            ->assertDontSee($otherMember->name)
            ->assertDontSee($ownLetter->document_path)
            ->assertDontSee('/storage/');
    }

    public function test_my_tasks_can_be_combined_with_search_and_status_filters(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $member = $this->makeUser('anggota_divisi', $division);
        $matchingLetter = $this->makeAssignedLetter(
            $divisionHead,
            $member,
            $division,
            'AGD-FILTER-COCOK',
            'Undangan koordinasi khusus',
        );
        $otherOwnLetter = $this->makeAssignedLetter(
            $divisionHead,
            $member,
            $division,
            'AGD-FILTER-LAIN',
            'Surat pemberitahuan biasa',
        );

        $this->actingAs($member)
            ->get(route('incoming-letters.index', [
                'my_tasks' => 1,
                'search' => 'koordinasi khusus',
                'status' => IncomingLetter::STATUS_DITUGASKAN_KE_ANGGOTA,
            ]))
            ->assertOk()
            ->assertSee($matchingLetter->agenda_number)
            ->assertDontSee($otherOwnLetter->agenda_number)
            ->assertSee('name="my_tasks" type="hidden" value="1"', false)
            ->assertSee('value="koordinasi khusus"', false)
            ->assertSee('<option value="ditugaskan_ke_anggota" selected>Ditugaskan ke Anggota</option>', false);
    }

    public function test_my_tasks_parameter_is_preserved_across_pagination(): void
    {
        $division = $this->makeDivision();
        $divisionHead = $this->makeUser('ketua_divisi', $division);
        $member = $this->makeUser('anggota_divisi', $division);

        foreach (range(1, 11) as $number) {
            $this->makeAssignedLetter(
                $divisionHead,
                $member,
                $division,
                'AGD-PAGE-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
            );
        }

        $this->actingAs($member)
            ->get(route('incoming-letters.index', ['my_tasks' => 1]))
            ->assertOk()
            ->assertViewHas(
                'incomingLetters',
                fn ($letters) => $letters->total() === 11
                    && str_contains($letters->url(2), 'my_tasks=1'),
            )
            ->assertSee('my_tasks=1', false)
            ->assertSee('page=2', false);
    }

    public function test_my_tasks_displays_its_specific_empty_state(): void
    {
        $division = $this->makeDivision();
        $member = $this->makeUser('anggota_divisi', $division);

        $this->actingAs($member)
            ->get(route('incoming-letters.index', ['my_tasks' => 1]))
            ->assertOk()
            ->assertSee('Belum ada surat yang ditugaskan kepada Anda.')
            ->assertDontSee('Belum ada Surat Masuk.');
    }

    public function test_complete_sprint_three_workflow_ends_in_the_members_my_tasks(): void
    {
        $destinationDivision = $this->makeDivision('Redaksi', 'RED');
        $admin = $this->makeUser('admin_surat', null, 'Admin Surat');
        $reviewer = $this->makeUser('pimpinan', null, 'Pimpinan');
        $divisionHead = $this->makeUser('ketua_divisi', $destinationDivision, 'Ketua Redaksi');
        $member = $this->makeUser('anggota_divisi', $destinationDivision, 'Anggota Terpilih');
        $otherMember = $this->makeUser('anggota_divisi', $destinationDivision, 'Anggota Lain');

        $this->actingAs($admin)
            ->post(route('incoming-letters.store'), $this->letterPayload())
            ->assertRedirect();

        $letter = IncomingLetter::query()->where('agenda_number', 'AGD-E2E-SPRINT-3')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('incoming-letters.submit-for-review', $letter))
            ->assertRedirect(route('incoming-letters.show', $letter));

        $this->actingAs($reviewer)
            ->post(route('incoming-letters.review.store', $letter), [
                'destination_division_id' => $destinationDivision->id,
                'review_note' => 'Teruskan kepada tim redaksi.',
            ])
            ->assertRedirect(route('incoming-letters.show', $letter));

        $this->actingAs($divisionHead)
            ->get(route('incoming-letters.assignment.create', $letter))
            ->assertOk()
            ->assertSee($member->name)
            ->assertSee($otherMember->name);

        $this->actingAs($divisionHead)
            ->post(route('incoming-letters.assignment.store', $letter), [
                'assigned_to' => $member->id,
                'instruction' => 'Siapkan respons surat.',
                'due_date' => null,
            ])
            ->assertRedirect(route('incoming-letters.show', $letter));

        $letter->refresh();

        $this->assertSame(IncomingLetter::STATUS_DITUGASKAN_KE_ANGGOTA, $letter->status);
        $this->assertSame($member->id, $letter->assignment?->assigned_to);
        $this->assertSame(4, $letter->statusHistories()->count());

        $this->actingAs($member)
            ->get(route('incoming-letters.index', ['my_tasks' => 1]))
            ->assertOk()
            ->assertSee('AGD-E2E-SPRINT-3')
            ->assertSee('Anggota Terpilih')
            ->assertSee('Ditugaskan ke Anggota')
            ->assertDontSee($letter->document_path);

        $this->actingAs($otherMember)
            ->get(route('incoming-letters.index', ['my_tasks' => 1]))
            ->assertOk()
            ->assertDontSee('AGD-E2E-SPRINT-3');
    }

    private function makeDivision(
        string $name = 'Redaksi',
        string $code = 'RED',
    ): Division {
        return Division::query()->create([
            'name' => $name,
            'code' => $code,
            'is_active' => true,
        ]);
    }

    private function makeUser(
        string $roleSlug,
        ?Division $division = null,
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
            'is_active' => true,
        ]);
    }

    private function makeAssignedLetter(
        User $assigner,
        User $assignee,
        Division $division,
        string $agendaNumber,
        string $subject = 'Surat tugas anggota',
    ): IncomingLetter {
        $letter = IncomingLetter::query()->create([
            'agenda_number' => $agendaNumber,
            'letter_number' => fake()->unique()->numerify('###/RS/VIII/2026'),
            'sender_name' => 'Instansi Pengirim',
            'addressed_to' => 'Radar Surat',
            'letter_date' => '2026-08-01',
            'received_date' => '2026-08-03',
            'received_via' => 'fisik',
            'subject' => $subject,
            'priority' => 'biasa',
            'destination_division_id' => $division->id,
            'document_path' => "incoming-letters/2026/{$agendaNumber}.pdf",
            'original_document_name' => 'surat.pdf',
            'document_mime_type' => 'application/pdf',
            'document_size' => 1024,
            'status' => IncomingLetter::STATUS_DITUGASKAN_KE_ANGGOTA,
            'created_by' => $assigner->id,
            'submitted_for_review_at' => now(),
        ]);

        $letter->assignment()->create([
            'assigned_by' => $assigner->id,
            'assigned_to' => $assignee->id,
            'division_id' => $division->id,
            'instruction' => null,
            'due_date' => null,
            'assigned_at' => now(),
        ]);

        return $letter;
    }

    /**
     * @return array<string, mixed>
     */
    private function letterPayload(): array
    {
        return [
            'agenda_number' => 'AGD-E2E-SPRINT-3',
            'letter_number' => '001/RS/VIII/2026',
            'sender_name' => 'Instansi Pengirim',
            'addressed_to' => 'Radar Surat',
            'letter_date' => '2026-08-01',
            'received_date' => '2026-08-03',
            'received_via' => 'fisik',
            'subject' => 'Alur lengkap Sprint 3',
            'priority' => 'segera',
            'destination_division_id' => null,
            'document' => UploadedFile::fake()->create('surat-sprint-3.pdf', 20, 'application/pdf'),
        ];
    }
}

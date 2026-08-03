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

class IncomingLetterBackendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_guests_cannot_access_incoming_letter_routes(): void
    {
        $this->get(route('incoming-letters.index'))
            ->assertRedirect(route('login'));
    }

    public function test_an_active_non_admin_can_only_read_incoming_letters(): void
    {
        $reader = $this->makeUser('anggota_divisi', 'Anggota Divisi');
        $letter = $this->makeLetter($reader);

        $this->actingAs($reader)
            ->get(route('incoming-letters.show', $letter))
            ->assertOk();

        $this->actingAs($reader)
            ->get(route('incoming-letters.create'))
            ->assertForbidden();
    }

    public function test_admin_surat_can_create_show_edit_and_update_an_incoming_letter(): void
    {
        $admin = $this->makeUser('admin_surat', 'Admin Surat');
        $division = $this->makeDivision();

        $this->actingAs($admin)
            ->get(route('incoming-letters.create'))
            ->assertOk()
            ->assertJsonPath('divisions.0.id', $division->id);

        $created = $this->actingAs($admin)
            ->post(route('incoming-letters.store'), $this->letterPayload([
                'agenda_number' => 'AGD-001',
                'destination_division_id' => $division->id,
            ]));

        $created->assertCreated()
            ->assertJsonPath('agenda_number', 'AGD-001')
            ->assertJsonPath('status', IncomingLetter::STATUS_BARU_DITERIMA);

        $letter = IncomingLetter::query()->firstOrFail();

        $this->actingAs($admin)
            ->get(route('incoming-letters.show', $letter))
            ->assertOk()
            ->assertJsonPath('creator.id', $admin->id)
            ->assertJsonPath('destination_division.id', $division->id);

        $this->actingAs($admin)
            ->get(route('incoming-letters.edit', $letter))
            ->assertOk()
            ->assertJsonPath('incoming_letter.id', $letter->id);

        $previousPath = $letter->document_path;

        $this->actingAs($admin)
            ->put(route('incoming-letters.update', $letter), $this->letterPayload([
                'agenda_number' => 'AGD-001-REV',
                'subject' => 'Perihal Diperbarui',
                'destination_division_id' => $division->id,
                'document' => UploadedFile::fake()->create('revisi.pdf', 20, 'application/pdf'),
            ]))
            ->assertOk()
            ->assertJsonPath('agenda_number', 'AGD-001-REV');

        $this->assertDatabaseHas('incoming_letters', [
            'id' => $letter->id,
            'agenda_number' => 'AGD-001-REV',
            'subject' => 'Perihal Diperbarui',
            'created_by' => $admin->id,
        ]);
        Storage::disk('local')->assertMissing($previousPath);
    }

    public function test_upload_is_stored_privately_with_its_metadata(): void
    {
        $admin = $this->makeUser('admin_surat', 'Admin Surat');

        $this->actingAs($admin)
            ->post(route('incoming-letters.store'), $this->letterPayload([
                'agenda_number' => 'AGD-UPLOAD',
                'received_date' => '2026-08-03',
                'document' => UploadedFile::fake()->create('dokumen-masuk.pdf', 100, 'application/pdf'),
            ]))
            ->assertCreated();

        $letter = IncomingLetter::query()->where('agenda_number', 'AGD-UPLOAD')->firstOrFail();

        Storage::disk('local')->assertExists($letter->document_path);
        $this->assertStringStartsWith('incoming-letters/2026/', $letter->document_path);
        $this->assertSame('dokumen-masuk.pdf', $letter->original_document_name);
        $this->assertSame('application/pdf', $letter->document_mime_type);
        $this->assertGreaterThan(0, $letter->document_size);
    }

    public function test_active_user_can_preview_and_download_an_incoming_letter(): void
    {
        $reader = $this->makeUser('anggota_divisi', 'Anggota Divisi');
        $letter = $this->makeLetter($reader, [
            'original_document_name' => 'surat-masuk.pdf',
            'document_mime_type' => 'application/pdf',
        ]);

        $this->actingAs($reader)
            ->get(route('incoming-letters.preview', $letter))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeaderContains('content-disposition', 'inline');

        $this->actingAs($reader)
            ->get(route('incoming-letters.download', $letter))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('surat-masuk.pdf');
    }

    public function test_index_searches_and_filters_incoming_letters(): void
    {
        $admin = $this->makeUser('admin_surat', 'Admin Surat');
        $marketing = $this->makeDivision('Pemasaran', 'PEM');
        $finance = $this->makeDivision('Keuangan', 'KEU');

        $matchingLetter = $this->makeLetter($admin, [
            'agenda_number' => 'AGD-CARI',
            'letter_number' => '001/RS/VIII/2026',
            'sender_name' => 'Kantor Pajak',
            'subject' => 'Undangan Rapat Pajak',
            'priority' => 'tinggi',
            'status' => IncomingLetter::STATUS_MENUNGGU_PEMERIKSAAN,
            'destination_division_id' => $marketing->id,
            'received_date' => '2026-08-03',
        ]);
        $this->makeLetter($admin, [
            'agenda_number' => 'AGD-LAIN',
            'sender_name' => 'Pemasok',
            'subject' => 'Kontrak Tahunan',
            'priority' => 'normal',
            'destination_division_id' => $finance->id,
            'received_date' => '2026-08-02',
        ]);

        $this->actingAs($admin)
            ->get(route('incoming-letters.index', ['search' => 'Pajak']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingLetter->id);

        $this->actingAs($admin)
            ->get(route('incoming-letters.index', [
                'status' => IncomingLetter::STATUS_MENUNGGU_PEMERIKSAAN,
                'priority' => 'tinggi',
                'destination_division_id' => $marketing->id,
                'received_date' => '2026-08-03',
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingLetter->id);
    }

    public function test_admin_surat_can_submit_a_new_letter_for_review(): void
    {
        $admin = $this->makeUser('admin_surat', 'Admin Surat');
        $letter = $this->makeLetter($admin);

        $this->actingAs($admin)
            ->patch(route('incoming-letters.submit-for-review', $letter))
            ->assertOk()
            ->assertJsonPath('status', IncomingLetter::STATUS_MENUNGGU_PEMERIKSAAN);

        $this->assertDatabaseHas('incoming_letters', [
            'id' => $letter->id,
            'status' => IncomingLetter::STATUS_MENUNGGU_PEMERIKSAAN,
        ]);
        $this->assertNotNull($letter->fresh()->submitted_for_review_at);
    }

    private function makeDivision(string $name = 'Redaksi', string $code = 'RED'): Division
    {
        return Division::query()->create([
            'name' => $name,
            'code' => $code,
            'is_active' => true,
        ]);
    }

    private function makeUser(string $roleSlug, string $roleName): User
    {
        $role = Role::query()->firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => $roleName],
        );

        return User::query()->create([
            'name' => $roleName,
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function letterPayload(array $overrides = []): array
    {
        return array_merge([
            'agenda_number' => 'AGD-'.fake()->unique()->numerify('####'),
            'letter_number' => '001/RS/VIII/2026',
            'sender_name' => 'Instansi Pengirim',
            'addressed_to' => 'Radar Surat',
            'letter_date' => '2026-08-01',
            'received_date' => '2026-08-03',
            'received_via' => 'Kurir',
            'subject' => 'Undangan Rapat',
            'summary' => 'Ringkasan surat masuk.',
            'priority' => 'normal',
            'destination_division_id' => null,
            'document' => UploadedFile::fake()->create('surat-masuk.pdf', 20, 'application/pdf'),
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeLetter(User $creator, array $overrides = []): IncomingLetter
    {
        $path = 'incoming-letters/2026/'.fake()->uuid().'.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 test document');

        return IncomingLetter::query()->create(array_merge([
            'agenda_number' => 'AGD-'.fake()->unique()->numerify('####'),
            'letter_number' => null,
            'sender_name' => 'Instansi Pengirim',
            'addressed_to' => 'Radar Surat',
            'letter_date' => '2026-08-01',
            'received_date' => '2026-08-03',
            'received_via' => 'Kurir',
            'subject' => 'Undangan Rapat',
            'summary' => null,
            'priority' => 'normal',
            'destination_division_id' => null,
            'document_path' => $path,
            'original_document_name' => 'surat.pdf',
            'document_mime_type' => 'application/pdf',
            'document_size' => Storage::disk('local')->size($path),
            'status' => IncomingLetter::STATUS_BARU_DITERIMA,
            'created_by' => $creator->id,
            'submitted_for_review_at' => null,
        ], $overrides));
    }
}

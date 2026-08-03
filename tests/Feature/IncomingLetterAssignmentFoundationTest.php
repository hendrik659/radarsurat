<?php

namespace Tests\Feature;

use App\Http\Requests\StoreIncomingLetterAssignmentRequest;
use App\Models\Division;
use App\Models\IncomingLetter;
use App\Models\IncomingLetterAssignment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;
use Tests\TestCase;

class IncomingLetterAssignmentFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_table_has_required_columns_unique_constraint_and_indexes(): void
    {
        $this->assertTrue(Schema::hasColumns('incoming_letter_assignments', [
            'id',
            'incoming_letter_id',
            'assigned_by',
            'assigned_to',
            'division_id',
            'instruction',
            'due_date',
            'assigned_at',
            'created_at',
            'updated_at',
        ]));

        $indexes = collect(Schema::getIndexes('incoming_letter_assignments'));

        $this->assertTrue($indexes->contains(
            fn (array $index): bool => $index['unique']
                && $index['columns'] === ['incoming_letter_id'],
        ));

        foreach (['assigned_by', 'assigned_to', 'division_id', 'due_date', 'assigned_at'] as $column) {
            $this->assertTrue($indexes->contains(
                fn (array $index): bool => $index['columns'] === [$column],
            ));
        }
    }

    public function test_assignment_relations_and_date_casts_work_and_a_letter_only_has_one_assignment(): void
    {
        $division = $this->makeDivision();
        $assigner = $this->makeUser('ketua_divisi', $division);
        $assignee = $this->makeUser('anggota_divisi', $division);
        $letter = $this->makeLetter($assigner, $division);

        $assignment = IncomingLetterAssignment::query()->create([
            'incoming_letter_id' => $letter->id,
            'assigned_by' => $assigner->id,
            'assigned_to' => $assignee->id,
            'division_id' => $division->id,
            'instruction' => 'Tindak lanjuti surat ini.',
            'due_date' => '2026-08-10',
            'assigned_at' => '2026-08-03 16:00:00',
        ]);

        $this->assertTrue($letter->assignment->is($assignment));
        $this->assertTrue($assignment->incomingLetter->is($letter));
        $this->assertTrue($assignment->assigner->is($assigner));
        $this->assertTrue($assignment->assignee->is($assignee));
        $this->assertTrue($assignment->division->is($division));
        $this->assertSame($letter->id, $assignment->incoming_letter_id);
        $this->assertSame($assigner->id, $assignment->assigned_by);
        $this->assertSame($assignee->id, $assignment->assigned_to);
        $this->assertSame($division->id, $assignment->division_id);
        $this->assertSame('2026-08-10', $assignment->due_date->format('Y-m-d'));
        $this->assertSame('2026-08-03 16:00:00', $assignment->assigned_at->format('Y-m-d H:i:s'));

        $this->expectException(QueryException::class);

        IncomingLetterAssignment::query()->create([
            'incoming_letter_id' => $letter->id,
            'assigned_by' => $assigner->id,
            'assigned_to' => $assignee->id,
            'division_id' => $division->id,
            'assigned_at' => now(),
        ]);
    }

    public function test_active_division_member_from_the_destination_division_is_accepted(): void
    {
        $division = $this->makeDivision();
        $creator = $this->makeUser('ketua_divisi', $division);
        $assignee = $this->makeUser('anggota_divisi', $division);
        $letter = $this->makeLetter($creator, $division);

        $validator = $this->validatorFor($letter, [
            'assigned_to' => $assignee->id,
            'instruction' => null,
            'due_date' => today()->toDateString(),
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_assigned_to_is_required(): void
    {
        $division = $this->makeDivision();
        $letter = $this->makeLetter($this->makeUser('ketua_divisi', $division), $division);
        $validator = $this->validatorFor($letter, []);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'Anggota yang ditugaskan wajib dipilih.',
            $validator->errors()->first('assigned_to'),
        );
    }

    public function test_inactive_division_member_is_rejected(): void
    {
        $division = $this->makeDivision();
        $creator = $this->makeUser('ketua_divisi', $division);
        $inactiveAssignee = $this->makeUser('anggota_divisi', $division, false);
        $letter = $this->makeLetter($creator, $division);
        $validator = $this->validatorFor($letter, ['assigned_to' => $inactiveAssignee->id]);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'Anggota yang ditugaskan harus memiliki akun aktif.',
            $validator->errors()->first('assigned_to'),
        );
    }

    public function test_user_without_division_member_role_is_rejected(): void
    {
        $division = $this->makeDivision();
        $creator = $this->makeUser('ketua_divisi', $division);
        $nonMember = $this->makeUser('pimpinan', $division);
        $letter = $this->makeLetter($creator, $division);
        $validator = $this->validatorFor($letter, ['assigned_to' => $nonMember->id]);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'Pengguna yang ditugaskan harus memiliki role Anggota Divisi.',
            $validator->errors()->first('assigned_to'),
        );
    }

    public function test_division_member_from_another_division_is_rejected(): void
    {
        $destinationDivision = $this->makeDivision('Redaksi', 'RED');
        $otherDivision = $this->makeDivision('Pemasaran', 'PEM');
        $creator = $this->makeUser('ketua_divisi', $destinationDivision);
        $otherAssignee = $this->makeUser('anggota_divisi', $otherDivision);
        $letter = $this->makeLetter($creator, $destinationDivision);
        $validator = $this->validatorFor($letter, ['assigned_to' => $otherAssignee->id]);

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'Anggota yang ditugaskan harus berasal dari divisi tujuan surat.',
            $validator->errors()->first('assigned_to'),
        );
    }

    public function test_instruction_is_optional_and_limited_to_2000_characters(): void
    {
        $division = $this->makeDivision();
        $creator = $this->makeUser('ketua_divisi', $division);
        $assignee = $this->makeUser('anggota_divisi', $division);
        $letter = $this->makeLetter($creator, $division);

        $emptyInstruction = $this->validatorFor($letter, [
            'assigned_to' => $assignee->id,
            'instruction' => null,
        ]);
        $maximumInstruction = $this->validatorFor($letter, [
            'assigned_to' => $assignee->id,
            'instruction' => str_repeat('a', 2000),
        ]);
        $longInstruction = $this->validatorFor($letter, [
            'assigned_to' => $assignee->id,
            'instruction' => str_repeat('a', 2001),
        ]);

        $this->assertTrue($emptyInstruction->passes());
        $this->assertTrue($maximumInstruction->passes());
        $this->assertTrue($longInstruction->fails());
        $this->assertSame(
            'Instruksi penugasan maksimal 2000 karakter.',
            $longInstruction->errors()->first('instruction'),
        );
    }

    public function test_due_date_is_optional_but_cannot_be_in_the_past(): void
    {
        $division = $this->makeDivision();
        $creator = $this->makeUser('ketua_divisi', $division);
        $assignee = $this->makeUser('anggota_divisi', $division);
        $letter = $this->makeLetter($creator, $division);

        $emptyDueDate = $this->validatorFor($letter, [
            'assigned_to' => $assignee->id,
            'due_date' => null,
        ]);
        $pastDueDate = $this->validatorFor($letter, [
            'assigned_to' => $assignee->id,
            'due_date' => today()->subDay()->toDateString(),
        ]);

        $this->assertTrue($emptyDueDate->passes());
        $this->assertTrue($pastDueDate->fails());
        $this->assertSame(
            'Batas waktu penugasan tidak boleh sebelum hari ini.',
            $pastDueDate->errors()->first('due_date'),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validatorFor(IncomingLetter $incomingLetter, array $data): Validator
    {
        $request = StoreIncomingLetterAssignmentRequest::create('/assignment', 'POST', $data);
        $route = new Route('POST', '/assignment', fn () => null);
        $route->bind($request);
        $route->setParameter('incomingLetter', $incomingLetter);
        $request->setRouteResolver(fn () => $route);

        $validator = ValidatorFacade::make(
            $request->all(),
            $request->rules(),
            $request->messages(),
        );

        foreach ($request->after() as $callback) {
            $validator->after($callback);
        }

        return $validator;
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
        Division $division,
        bool $isActive = true,
    ): User {
        $role = Role::query()->firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => str($roleSlug)->replace('_', ' ')->title()->toString()],
        );

        return User::query()->create([
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role_id' => $role->id,
            'division_id' => $division->id,
            'is_active' => $isActive,
        ]);
    }

    private function makeLetter(User $creator, Division $destinationDivision): IncomingLetter
    {
        return IncomingLetter::query()->create([
            'agenda_number' => 'AGD-'.fake()->unique()->numerify('####'),
            'letter_number' => '001/RS/VIII/2026',
            'sender_name' => 'Instansi Pengirim',
            'addressed_to' => 'Radar Surat',
            'letter_date' => '2026-08-01',
            'received_date' => '2026-08-03',
            'received_via' => 'fisik',
            'subject' => 'Surat untuk penugasan anggota',
            'priority' => 'biasa',
            'destination_division_id' => $destinationDivision->id,
            'document_path' => 'incoming-letters/2026/surat.pdf',
            'original_document_name' => 'surat.pdf',
            'document_mime_type' => 'application/pdf',
            'document_size' => 1024,
            'status' => IncomingLetter::STATUS_DITERUSKAN_KE_DIVISI,
            'created_by' => $creator->id,
            'submitted_for_review_at' => now(),
        ]);
    }
}

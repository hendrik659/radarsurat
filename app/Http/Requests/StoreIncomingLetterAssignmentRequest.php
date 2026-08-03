<?php

namespace App\Http\Requests;

use App\Models\IncomingLetter;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class StoreIncomingLetterAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $incomingLetter = $this->route('incomingLetter');

        if (! $this->user() || ! $incomingLetter instanceof IncomingLetter) {
            return false;
        }

        Gate::forUser($this->user())->authorize('assign', $incomingLetter);

        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
            'instruction' => ['nullable', 'string', 'max:2000'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('assigned_to')) {
                    return;
                }

                $incomingLetter = $this->route('incomingLetter');
                $assignee = User::query()
                    ->with('role:id,slug')
                    ->find($this->integer('assigned_to'));

                if (! $assignee) {
                    $validator->errors()->add(
                        'assigned_to',
                        'Anggota yang ditugaskan tidak ditemukan.',
                    );

                    return;
                }

                if (! $assignee->is_active) {
                    $validator->errors()->add(
                        'assigned_to',
                        'Anggota yang ditugaskan harus memiliki akun aktif.',
                    );

                    return;
                }

                if ($assignee->role?->slug !== 'anggota_divisi') {
                    $validator->errors()->add(
                        'assigned_to',
                        'Pengguna yang ditugaskan harus memiliki role Anggota Divisi.',
                    );

                    return;
                }

                if (! $incomingLetter instanceof IncomingLetter
                    || $assignee->division_id !== $incomingLetter->destination_division_id) {
                    $validator->errors()->add(
                        'assigned_to',
                        'Anggota yang ditugaskan harus berasal dari divisi tujuan surat.',
                    );
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'assigned_to.required' => 'Anggota yang ditugaskan wajib dipilih.',
            'assigned_to.integer' => 'Anggota yang ditugaskan tidak valid.',
            'assigned_to.exists' => 'Anggota yang ditugaskan tidak ditemukan.',
            'instruction.string' => 'Instruksi penugasan harus berupa teks.',
            'instruction.max' => 'Instruksi penugasan maksimal 2000 karakter.',
            'due_date.date' => 'Batas waktu penugasan harus berupa tanggal yang valid.',
            'due_date.after_or_equal' => 'Batas waktu penugasan tidak boleh sebelum hari ini.',
        ];
    }
}

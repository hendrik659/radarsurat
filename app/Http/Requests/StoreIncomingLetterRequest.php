<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomingLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'agenda_number' => ['required', 'string', 'max:100', 'unique:incoming_letters,agenda_number'],
            'letter_number' => ['nullable', 'string', 'max:100'],
            'sender_name' => ['required', 'string', 'max:255'],
            'addressed_to' => ['required', 'string', 'max:255'],
            'letter_date' => ['nullable', 'date'],
            'received_date' => ['required', 'date'],
            'received_via' => ['required', 'string', 'max:100'],
            'subject' => ['required', 'string', 'max:500'],
            'summary' => ['nullable', 'string'],
            'priority' => ['required', 'string', 'max:50'],
            'destination_division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}

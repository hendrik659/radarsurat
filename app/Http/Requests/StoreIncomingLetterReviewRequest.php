<?php

namespace App\Http\Requests;

use App\Models\IncomingLetter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreIncomingLetterReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $incomingLetter = $this->route('incomingLetter');

        if (! $this->user() || ! $incomingLetter instanceof IncomingLetter) {
            return false;
        }

        Gate::forUser($this->user())->authorize('review', $incomingLetter);

        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'destination_division_id' => [
                'required',
                'integer',
                Rule::exists('divisions', 'id')->where('is_active', true),
            ],
            'review_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

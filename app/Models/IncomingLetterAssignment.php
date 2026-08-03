<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingLetterAssignment extends Model
{
    protected $fillable = [
        'incoming_letter_id',
        'assigned_by',
        'assigned_to',
        'division_id',
        'instruction',
        'due_date',
        'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'incoming_letter_id' => 'integer',
            'assigned_by' => 'integer',
            'assigned_to' => 'integer',
            'division_id' => 'integer',
            'due_date' => 'date',
            'assigned_at' => 'datetime',
        ];
    }

    public function incomingLetter(): BelongsTo
    {
        return $this->belongsTo(IncomingLetter::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }
}

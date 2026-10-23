<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    public const STATUSES = ['tentative_hold', 'confirmed', 'planning', 'ready_for_event', 'in_progress', 'completed', 'cancelled'];

    public const TRANSITIONS = [
        'tentative_hold' => ['confirmed', 'cancelled'],
        'confirmed' => ['planning', 'cancelled'],
        'planning' => ['ready_for_event', 'cancelled'],
        'ready_for_event' => ['in_progress', 'cancelled'],
        'in_progress' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    protected $fillable = [
        'reference', 'inquiry_id', 'customer_id', 'title', 'category', 'event_date', 'start_time', 'end_time',
        'venue', 'city', 'guest_count', 'event_manager_id', 'status', 'held_by', 'hold_expires_at',
        'confirmed_at', 'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'hold_expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'event_manager_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(FollowUpTask::class);
    }
}

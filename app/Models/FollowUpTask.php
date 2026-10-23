<?php

namespace App\Models;

use Database\Factories\FollowUpTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUpTask extends Model
{
    /** @use HasFactory<FollowUpTaskFactory> */
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'inquiry_id', 'event_id', 'customer_id', 'assigned_to', 'due_at',
        'priority', 'status', 'completion_notes', 'completed_at',
    ];

    protected function casts(): array
    {
        return ['due_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}

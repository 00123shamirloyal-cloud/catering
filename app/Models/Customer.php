<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'email', 'phone', 'preferred_contact', 'lead_source', 'assigned_to', 'notes'];

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(FollowUpTask::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}

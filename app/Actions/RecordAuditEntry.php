<?php

namespace App\Actions;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RecordAuditEntry
{
    /** @param array<string, mixed> $properties */
    public function handle(Model $subject, string $description, array $properties = []): ActivityLog
    {
        return ActivityLog::query()->create([
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'actor_id' => Auth::id(),
            'description' => $description,
            'properties' => $properties,
        ]);
    }
}

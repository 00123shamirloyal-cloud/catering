<?php

namespace App\Models;

use Database\Factories\ContentEntryVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentEntryVersion extends Model
{
    /** @use HasFactory<ContentEntryVersionFactory> */
    use HasFactory;

    protected $fillable = ['content_entry_id', 'version_number', 'snapshot', 'created_by'];

    protected function casts(): array
    {
        return ['snapshot' => 'array'];
    }

    public function contentEntry(): BelongsTo
    {
        return $this->belongsTo(ContentEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

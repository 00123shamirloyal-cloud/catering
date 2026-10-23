<?php

namespace App\Models;

use Database\Factories\ContentEntryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentEntry extends Model
{
    /** @use HasFactory<ContentEntryFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type', 'title', 'slug', 'category', 'excerpt', 'body', 'image_path', 'metadata',
        'status', 'is_featured', 'is_demo', 'sort_order', 'published_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_featured' => 'boolean',
            'is_demo' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function inquiries(): BelongsToMany
    {
        return $this->belongsToMany(Inquiry::class, 'inquiry_content_entry')->withPivot('selection_type');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ContentEntryVersion::class)->orderByDesc('version_number');
    }
}

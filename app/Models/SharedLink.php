<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SharedLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'document_id',
        'created_by',
        'password',
        'expires_at',
        'download_count',
        'max_downloads',
        'is_active',
        'is_read',
        'read_at',
        'opened_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope untuk link aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    // Check if link is valid for download
    public function canDownload(): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_downloads && $this->download_count >= $this->max_downloads) return false;

        return true;
    }

    // Increment download count
    public function incrementDownload(): void
    {
        $this->increment('download_count');
    }

    // Get share URL
    // public function getShareUrl(): string
    // {
    //     return route('shared.document', $this->uuid);
    // }
}

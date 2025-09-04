<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'original_name',
        'file_path',
        'file_size',
        'mime_type',
        'extension',
        'folder_id',
        'uploaded_by',
        'description',
        'version',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function sharedLinks(): HasMany
    {
        return $this->hasMany(SharedLink::class);
    }

    // Scope untuk dokumen aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper method untuk format file size
    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < 4; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Get file URL
    public function getFileUrl(): string
    {
        return asset('storage/' . $this->file_path);
    }
}

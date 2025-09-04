<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // Only use created_at

    protected $fillable = [
        'document_id',
        'version_number',
        'name',
        'file_path',
        'file_size',
        'uploaded_by',
        'changes_description'
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
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

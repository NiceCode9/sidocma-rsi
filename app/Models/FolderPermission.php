<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolderPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'folder_id',
        'unit_id',
        'user_id',
        'can_read',
        'can_write',
        'can_delete',
        'can_share',
        'created_by'
    ];

    protected $casts = [
        'can_read' => 'boolean',
        'can_write' => 'boolean',
        'can_delete' => 'boolean',
        'can_share' => 'boolean'
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

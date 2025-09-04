<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // Only use created_at

    protected $fillable = [
        'user_id',
        'action',
        'target_type',
        'target_id',
        'target_name',
        'ip_address',
        'user_agent'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Get target model
    public function target()
    {
        return $this->morphTo('target', 'target_type', 'target_id');
    }

    // Constants for actions
    const ACTION_CREATE = 'create';
    const ACTION_READ = 'read';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_DOWNLOAD = 'download';
    const ACTION_SHARE = 'share';
    const ACTION_UPLOAD = 'upload';
}

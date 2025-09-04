<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'unit_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function createdFolders(): HasMany
    {
        return $this->hasMany(Folder::class, 'created_by');
    }

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function folderPermissions(): HasMany
    {
        return $this->hasMany(FolderPermission::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // public function sharedLinks(): HasMany
    // {
    //     return $this->hasMany(SharedLink::class, 'created_by');
    // }

    public function sharedLinksReceived(): HasMany
    {
        return $this->hasMany(SharedLink::class, 'shared_to');
    }

    public function sharedLinksSent(): HasMany
    {
        return $this->hasMany(SharedLink::class, 'shared_by');
    }

    public function documentVersions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class, 'uploaded_by');
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isDirektur(): bool
    {
        return $this->hasRole('direktur');
    }

    public function isKepalaUnit(): bool
    {
        return $this->hasRole('kepala unit');
    }

    public function isAnggotaUnit(): bool
    {
        return $this->hasRole('anggota unit');
    }

    public function canAccessAllFolders(): bool
    {
        return $this->isAdmin() || $this->isDirektur();
    }
}

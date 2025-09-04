<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'code'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    public function folderPermissions(): HasMany
    {
        return $this->hasMany(FolderPermission::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'path',
        'level',
        'created_by',
        'unit_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(FolderPermission::class);
    }

    // Scope untuk folder aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk root folders
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    // Helper method untuk update path
    public function updatePath(): void
    {
        if ($this->parent_id) {
            $parent = $this->parent;
            $this->path = $parent->path . '/' . $this->id;
            $this->level = $parent->level + 1;
        } else {
            $this->path = '/' . $this->id;
            $this->level = 0;
        }
        $this->save();
    }

    // Get breadcrumb path
    public function getBreadcrumb(): \Illuminate\Support\Collection
    {
        $ids = array_filter(explode('/', $this->path));
        $ids = array_map('intval', $ids);
        return self::whereIn('id', $ids)->orderBy('level')->get();
    }
}

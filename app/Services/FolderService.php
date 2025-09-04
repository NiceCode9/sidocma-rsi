<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Support\Str;

class FolderService
{
    /**
     * Create new folder
     */
    public function createFolder(array $data, User $creator): Folder
    {
        $folder = new Folder([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'created_by' => $creator->id,
            'unit_id' => $data['unit_id'] ?? null,
            'is_active' => $data['is_active'] ?? true
        ]);

        // Set default path dan level untuk menghindari NOT NULL constraint
        if ($folder->parent_id) {
            $parent = Folder::find($folder->parent_id);
            $folder->level = $parent ? $parent->level + 1 : 0;
            // Temporary path yang akan diupdate setelah save
            $folder->path = $parent ? $parent->path . '/temp' : '/temp';
        } else {
            $folder->level = 0;
            $folder->path = '/temp';
        }

        $folder->save();
        $folder->updatePath();

        return $folder;
    }

    /**
     * Get folder tree untuk user
     */
    public function getFolderTree(User $user, ?Folder $parentFolder = null): array
    {
        $permissionService = new PermissionService();
        $accessibleFolders = $permissionService->getAccessibleFolders($user);

        if ($parentFolder) {
            $folders = $accessibleFolders->where('parent_id', $parentFolder->id);
        } else {
            $folders = $accessibleFolders->whereNull('parent_id');
        }

        $tree = [];
        foreach ($folders as $folder) {
            $tree[] = [
                'folder' => $folder,
                'children' => $this->getFolderTree($user, $folder),
                'documents_count' => $folder->documents()->active()->count(),
                'can_write' => $permissionService->hasPermission($user, $folder, 'can_write'),
                'can_delete' => $permissionService->hasPermission($user, $folder, 'can_delete'),
                'can_share' => $permissionService->hasPermission($user, $folder, 'can_share')
            ];
        }

        return $tree;
    }

    /**
     * Move folder ke parent baru
     */
    public function moveFolder(Folder $folder, ?Folder $newParent): bool
    {
        $folder->parent_id = $newParent ? $newParent->id : null;
        $folder->save();
        $folder->updatePath();

        // Update path untuk semua children
        $this->updateChildrenPaths($folder);

        return true;
    }

    /**
     * Update paths untuk semua children folder
     */
    private function updateChildrenPaths(Folder $folder): void
    {
        $children = $folder->children;
        foreach ($children as $child) {
            $child->updatePath();
            $this->updateChildrenPaths($child);
        }
    }

    /**
     * Delete folder dan semua contents
     */
    public function deleteFolder(Folder $folder): bool
    {
        // Soft delete folder (ubah is_active menjadi false)
        $folder->update(['is_active' => false]);

        // Soft delete semua children folders
        $this->deactivateChildren($folder);

        // Soft delete semua documents dalam folder
        $folder->documents()->update(['is_active' => false]);

        return true;
    }

    /**
     * Deactivate semua children folder secara rekursif
     */
    private function deactivateChildren(Folder $folder): void
    {
        $children = $folder->children;
        foreach ($children as $child) {
            $child->update(['is_active' => false]);
            $child->documents()->update(['is_active' => false]);
            $this->deactivateChildren($child);
        }
    }
}

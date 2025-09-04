<?php

namespace App\Services;

use App\Models\User;
use App\Models\Folder;
use App\Models\Document;
use App\Models\FolderPermission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class PermissionService
{
    /**
     * Check if user can access folder
     */
    public function canAccessFolder(User $user, Folder $folder): bool
    {
        // Admin dan Direktur bisa akses semua folder
        if ($user->canAccessAllFolders()) {
            return true;
        }

        // Folder umum (tanpa unit) bisa diakses semua user
        if (is_null($folder->unit_id)) {
            return true;
        }

        // User bisa akses folder dari unit mereka
        if ($folder->unit_id === $user->unit_id) {
            return true;
        }

        // Check permission khusus
        return $this->hasSpecificPermission($user, $folder, 'can_read');
    }

    /**
     * Check specific permission (read, write, delete, share)
     */
    public function hasPermission(User $user, Folder $folder, string $permission): bool
    {
        // Admin selalu punya semua permission
        if ($user->isAdmin()) {
            return true;
        }

        // Direktur punya semua permission kecuali delete
        if ($user->isDirektur() && $permission !== 'can_delete') {
            return true;
        }

        // Check basic access first
        if (!$this->canAccessFolder($user, $folder)) {
            return false;
        }

        // Check specific permission
        return $this->hasSpecificPermission($user, $folder, $permission);
    }

    /**
     * Check specific permission dari folder_permissions table
     */
    private function hasSpecificPermission(User $user, Folder $folder, string $permission): bool
    {
        // Check user-specific permission
        $userPermission = FolderPermission::where('folder_id', $folder->id)
            ->where('user_id', $user->id)
            ->first();

        if ($userPermission) {
            return $userPermission->{$permission};
        }

        // Check unit & role permission
        $unitPermission = FolderPermission::where('folder_id', $folder->id)
            ->where('unit_id', $user->unit_id)
            ->first();

        if ($unitPermission) {
            return $unitPermission->{$permission};
        }

        // Default permissions berdasarkan role
        return $this->getDefaultPermission($user, $permission);
    }

    /**
     * Get default permission berdasarkan role
     */
    private function getDefaultPermission(User $user, string $permission): bool
    {
        switch ($user->getRoleNames()->first()) {
            case 'admin':
                return true;
            case 'direktur':
                return $permission !== 'can_delete';
            case 'kepala unit':
                return in_array($permission, ['can_read', 'can_write', 'can_share']);
            case 'anggota unit':
                return $permission === 'can_read';
            default:
                return false;
        }
    }

    /**
     * Get accessible folders untuk user
     */
    public function getAccessibleFolders(User $user): Collection
    {
        if ($user->canAccessAllFolders()) {
            return Folder::active()->get();
        }

        // Get folders berdasarkan unit user
        $unitFolders = Folder::active()
            ->where(function ($query) use ($user) {
                $query->whereNull('unit_id') // folder umum
                    ->orWhere('unit_id', $user->unit_id); // folder unit user
            })
            ->get();

        // Get folders dengan permission khusus
        $permissionFolderIds = FolderPermission::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere(function ($q) use ($user) {
                    $q->where('unit_id', $user->unit_id);
                });
        })
            ->where('can_read', true)
            ->pluck('folder_id');

        $permissionFolders = Folder::active()
            ->whereIn('id', $permissionFolderIds)
            ->get();

        return $unitFolders->merge($permissionFolders)->unique('id');
    }

    /**
     * Get accessible documents untuk user
     */
    public function getAccessibleDocuments(User $user, ?Folder $folder = null): Collection
    {
        $query = Document::active()->with(['folder', 'uploader']);

        if ($folder) {
            // Get documents dari folder tertentu
            $query->where('folder_id', $folder->id);
        } else {
            // Get semua accessible documents
            $accessibleFolderIds = $this->getAccessibleFolders($user)->pluck('id');
            $query->whereIn('folder_id', $accessibleFolderIds);
        }

        return $query->get();
    }

    /**
     * Grant permission ke folder untuk unit/role/user
     */
    public function grantFolderPermission(
        Folder $folder,
        array $permissions,
        User $grantedBy,
        ?int $unitId = null,
        ?int $roleId = null,
        ?int $userId = null
    ): FolderPermission {
        return FolderPermission::create([
            'folder_id' => $folder->id,
            'unit_id' => $unitId,
            // 'role_id' => $roleId,
            'user_id' => $userId,
            'can_read' => $permissions['can_read'] ?? false,
            'can_write' => $permissions['can_write'] ?? false,
            'can_delete' => $permissions['can_delete'] ?? false,
            'can_share' => $permissions['can_share'] ?? false,
            'created_by' => $grantedBy->id
        ]);
    }

    /**
     * Revoke permission dari folder
     */
    public function revokeFolderPermission(
        Folder $folder,
        ?int $unitId = null,
        ?int $roleId = null,
        ?int $userId = null
    ): bool {
        $query = FolderPermission::where('folder_id', $folder->id);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('unit_id', $unitId);
            // ->where('role_id', $roleId);
        }

        return $query->delete() > 0;
    }

    /**
     * Check if user can access document
     */
    public function canAccessDocument(User $user, Document $document): bool
    {
        return $this->canAccessFolder($user, $document->folder);
    }

    /**
     * Check if user can download document
     */
    public function canDownloadDocument(User $user, Document $document): bool
    {
        return $this->hasPermission($user, $document->folder, 'can_read');
    }

    /**
     * Check if user can upload to folder
     */
    public function canUploadToFolder(User $user, Folder $folder): bool
    {
        return $this->hasPermission($user, $folder, 'can_write');
    }

    /**
     * Check if user can delete document
     */
    public function canDeleteDocument(User $user, Document $document): bool
    {
        // User bisa delete dokumen yang mereka upload sendiri (kecuali anggota unit)
        if ($document->uploaded_by === $user->id && !$user->isAnggotaUnit()) {
            return true;
        }

        return $this->hasPermission($user, $document->folder, 'can_delete');
    }

    /**
     * Check if user can create folder
     */
    public function canCreateFolder(User $user, ?Folder $parentFolder = null): bool
    {
        // Hanya admin yang bisa create folder
        if ($user->isAdmin()) {
            return true;
        }

        // Kepala unit bisa create subfolder dalam unit mereka
        if ($user->isKepalaUnit() && $parentFolder) {
            return $parentFolder->unit_id === $user->unit_id;
        }

        return false;
    }

    /**
     * Check if user can manage folder permissions
     */
    public function canManageFolderPermissions(User $user, Folder $folder): bool
    {
        // Hanya admin yang bisa manage permissions
        return $user->isAdmin();
    }
}

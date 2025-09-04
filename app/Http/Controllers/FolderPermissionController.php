<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Unit;
use App\Models\User;
use App\Models\FolderPermission;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class FolderPermissionController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
        // $this->middleware('auth');
        // $this->middleware('can:manage-folder-permissions');
    }

    /**
     * Show permission management interface
     */
    public function show(Folder $folder)
    {
        $folder->load(['permissions.unit', 'permissions.role', 'permissions.user']);
        $units = Unit::all();
        $roles = Role::all();
        $users = User::with(['unit', 'role'])->where('is_active', true)->get();

        return view('folders.partials.permissions', compact('folder', 'units', 'roles', 'users'));
    }

    /**
     * Grant permission to unit/role/user
     */
    public function grant(Request $request, Folder $folder)
    {
        $request->validate([
            'type' => 'required|in:unit,role,user',
            'target_id' => 'required|integer',
            'role_id' => 'nullable|integer|exists:roles,id',
            'can_read' => 'boolean',
            'can_write' => 'boolean',
            'can_delete' => 'boolean',
            'can_share' => 'boolean',
        ]);

        try {
            $unitId = null;
            $roleId = null;
            $userId = null;

            switch ($request->type) {
                case 'unit':
                    $unitId = $request->target_id;
                    $roleId = $request->role_id;
                    break;
                case 'role':
                    $roleId = $request->target_id;
                    break;
                case 'user':
                    $userId = $request->target_id;
                    break;
            }

            $this->permissionService->grantFolderPermission(
                $folder,
                [
                    'can_read' => $request->boolean('can_read'),
                    'can_write' => $request->boolean('can_write'),
                    'can_delete' => $request->boolean('can_delete'),
                    'can_share' => $request->boolean('can_share'),
                ],
                Auth::user(),
                $unitId,
                $roleId,
                $userId
            );

            return response()->json([
                'success' => true,
                'message' => 'Permission berhasil diberikan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memberikan permission: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Revoke permission
     */
    public function revoke(Request $request, Folder $folder)
    {
        $request->validate([
            'permission_id' => 'required|exists:folder_permissions,id'
        ]);

        try {
            FolderPermission::destroy($request->permission_id);

            return response()->json([
                'success' => true,
                'message' => 'Permission berhasil dicabut'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencabut permission: ' . $e->getMessage()
            ], 422);
        }
    }
}

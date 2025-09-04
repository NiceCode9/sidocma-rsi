<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\Unit;
use App\Services\FolderService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FolderController extends Controller
{
    protected $folderService;
    protected $permissionService;

    public function __construct(FolderService $folderService, PermissionService $permissionService)
    {
        $this->folderService = $folderService;
        $this->permissionService = $permissionService;
    }

    /**
     * Display folder browser page
     */
    public function index()
    {
        return view('folders.browse');
    }

    /**
     * Browse folder content (for AJAX)
     */
    public function browse(Request $request, Folder $folder = null)
    {
        $user = Auth::user();
        $limit = $request->get('limit', 10);
        $offset = $request->get('offset', 0);

        // Check permission if accessing specific folder
        if ($folder && !$this->permissionService->canAccessFolder($user, $folder)) {
            return response()->json(['error' => 'Tidak memiliki akses ke folder ini'], 403);
        }

        // Get all accessible folders first
        $foldersQuery = $folder ? $folder->children() : Folder::root();
        $allFolders = $foldersQuery
            ->with(['creator', 'unit'])
            ->active()
            ->get()
            ->filter(function ($folder) use ($user) {
                return $this->permissionService->canAccessFolder($user, $folder);
            });

        // Get all documents if in specific folder
        $allDocuments = collect([]);
        if ($folder) {
            $allDocuments = $folder->documents()->active()->get();
        }

        // Combine folders and documents
        $allItems = collect();

        // Add folders first
        $allFolders->each(function ($folder) use ($allItems) {
            $allItems->push([
                'id' => $folder->id,
                'name' => $folder->name,
                'description' => $folder->description,
                'unit' => $folder->unit ? $folder->unit->name : null,
                'creator' => $folder->creator->name,
                'created_at' => $folder->created_at->format('d M Y'),
                'documents_count' => $folder->documents()->active()->count(),
                'subfolders_count' => $folder->children()->active()->count(),
                'total_size' => $folder->documents()->active()->sum('file_size'),
                'type' => 'folder',
                'sort_order' => 1 // Folders first
            ]);
        });

        // Add documents
        $allDocuments->each(function ($document) use ($allItems) {
            $allItems->push([
                'id' => $document->id,
                'name' => $document->name,
                'original_name' => $document->original_name,
                'file_size' => $document->file_size,
                'extension' => $document->extension,
                'created_at' => $document->created_at->format('d M Y'),
                'mime_type' => $document->mime_type,
                'type' => 'document',
                'sort_order' => 2 // Documents after folders
            ]);
        });

        // Sort by type (folders first) then by name
        $allItems = $allItems->sortBy([
            ['sort_order', 'asc'],
            ['name', 'asc']
        ]);

        // Apply offset and limit
        $items = $allItems->skip($offset)->take($limit);
        $hasMore = $allItems->count() > ($offset + $limit);

        // Separate folders and documents for response
        $folders = $items->where('type', 'folder')->values();
        $documents = $items->where('type', 'document')->values();

        // Get breadcrumb
        $breadcrumb = [];
        if ($folder) {
            $breadcrumb = $folder->getBreadcrumb()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name
                ];
            });
        }

        return response()->json([
            'folders' => $folders,
            'documents' => $documents,
            'breadcrumb' => $breadcrumb,
            'current_folder' => $folder ? [
                'id' => $folder->id,
                'name' => $folder->name,
                'description' => $folder->description
            ] : null,
            'has_more' => $hasMore,
            'total_items' => $allItems->count(),
            'loaded_items' => $offset + $items->count(),
            'offset' => $offset,
            'limit' => $limit
        ]);
    }

    /**
     * Create new folder
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:folders,id',
            'unit_id' => 'nullable|exists:units,id'
        ]);

        $user = Auth::user();

        // Check permission to create folder
        $parentFolder = $request->parent_id ? Folder::find($request->parent_id) : null;
        if (!$this->permissionService->canCreateFolder($user, $parentFolder)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk membuat folder'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $folder = $this->folderService->createFolder([
                'name' => $request->name,
                'description' => $request->description,
                'parent_id' => $request->parent_id,
                'unit_id' => $request->unit_id ?? $user->unit_id
            ], $user);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil dibuat',
                'folder' => [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'description' => $folder->description
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat folder: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get units for folder creation
     */
    public function getUnits()
    {
        $units = Unit::select('id', 'name')->get();
        return response()->json($units);
    }

    /**
     * Delete folder
     */
    public function destroy(Folder $folder)
    {
        $user = Auth::user();

        if (!$this->permissionService->hasPermission($user, $folder, 'can_delete')) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk menghapus folder'
            ], 403);
        }

        try {
            DB::beginTransaction();
            $this->folderService->deleteFolder($folder);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Folder berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus folder: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Search folders and documents
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $user = Auth::user();
        $accessibleFolders = $this->permissionService->getAccessibleFolders($user);

        if (!$query) {
            return response()->json(['folders' => [], 'documents' => []]);
        }

        // Search folders
        $folders = $accessibleFolders
            ->filter(function ($folder) use ($query) {
                return stripos($folder->name, $query) !== false;
            })
            ->take(10)
            ->map(function ($folder) {
                return [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'description' => $folder->description,
                    'type' => 'folder',
                    'path' => $folder->getBreadcrumb()->pluck('name')->implode('/')
                ];
            });

        // Search documents
        $documents = $this->permissionService->getAccessibleDocuments($user)
            ->filter(function ($document) use ($query) {
                return stripos($document->name, $query) !== false ||
                    stripos($document->original_name, $query) !== false;
            })
            ->take(10)
            ->map(function ($document) {
                return [
                    'id' => $document->id,
                    'name' => $document->name,
                    'original_name' => $document->original_name,
                    'type' => 'document',
                    'folder_name' => $document->folder->name
                ];
            });

        return response()->json([
            'folders' => $folders->values(),
            'documents' => $documents->values(),
        ]);
    }

    /**
     * Format file size
     */
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < 4; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

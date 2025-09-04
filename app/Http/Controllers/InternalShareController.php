<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\SharedLink;
use App\Models\User;
use App\Services\PermissionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InternalShareController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Share document to internal users
     */
    public function shareDocument(Request $request, Document $document)
    {
        $user = Auth::user();

        // Check permission to share document
        if (!$this->permissionService->canAccessFolder($user, $document->folder)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk membagikan dokumen ini'
            ], 403);
        }

        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'in:view,download,share',
            'message' => 'nullable|string|max:500',
            'expires_in_days' => 'nullable|integer|min:1|max:365'
        ]);

        try {
            DB::beginTransaction();

            $expiresAt = null;
            if ($request->expires_in_days) {
                $expiresAt = Carbon::now()->addDays($request->expires_in_days);
            }

            $shareCount = 0;
            foreach ($request->user_ids as $userId) {
                // Skip if trying to share to self
                if ($userId == $user->id) continue;

                // Check if already shared to this user (deactivate old shares)
                SharedLink::where('document_id', $document->id)
                    ->where('shared_by', $user->id)
                    ->where('shared_to', $userId)
                    ->update(['is_active' => false]);

                // Create new share
                SharedLink::create([
                    'document_id' => $document->id,
                    'shared_by' => $user->id,
                    'shared_to' => $userId,
                    'permissions' => $request->permissions,
                    'message' => $request->message,
                    'expires_at' => $expiresAt,
                    'is_read' => false,
                    'is_active' => true
                ]);

                $shareCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Dokumen berhasil dibagikan ke {$shareCount} user"
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membagikan dokumen: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get shared documents (received by current user)
     */
    public function getSharedWithMe(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 10);

        $sharedDocs = SharedLink::with(['document', 'sharedBy'])
            ->where('shared_to', $user->id)
            ->active()
            ->orderBy('is_read', 'asc') // Unread first
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $data = $sharedDocs->map(function ($share) {
            return [
                'id' => $share->id,
                'document' => [
                    'id' => $share->document->id,
                    'name' => $share->document->name,
                    'original_name' => $share->document->original_name,
                    'file_size' => $share->document->file_size,
                    'extension' => pathinfo($share->document->original_name, PATHINFO_EXTENSION)
                ],
                'shared_by' => [
                    'id' => $share->sharedBy->id,
                    'name' => $share->sharedBy->name
                ],
                'permissions' => $share->permissions,
                'message' => $share->message,
                'is_read' => $share->is_read,
                'expires_at' => $share->expires_at?->format('d M Y H:i'),
                'shared_at' => $share->created_at->format('d M Y H:i')
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $sharedDocs->currentPage(),
                'last_page' => $sharedDocs->lastPage(),
                'total' => $sharedDocs->total()
            ]
        ]);
    }

    /**
     * Get documents shared by current user
     */
    public function getSharedByMe(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 10);

        $sharedDocs = SharedLink::with(['document', 'sharedTo'])
            ->where('shared_by', $user->id)
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $data = $sharedDocs->map(function ($share) {
            return [
                'id' => $share->id,
                'document' => [
                    'id' => $share->document->id,
                    'name' => $share->document->name,
                    'original_name' => $share->document->original_name
                ],
                'shared_to' => [
                    'id' => $share->sharedTo->id,
                    'name' => $share->sharedTo->name
                ],
                'permissions' => $share->permissions,
                'message' => $share->message,
                'is_read' => $share->is_read,
                'expires_at' => $share->expires_at?->format('d M Y H:i'),
                'shared_at' => $share->created_at->format('d M Y H:i')
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $sharedDocs->currentPage(),
                'last_page' => $sharedDocs->lastPage(),
                'total' => $sharedDocs->total()
            ]
        ]);
    }

    /**
     * Access shared document (mark as read and download/view)
     */
    public function accessSharedDocument($shareId, $action = 'view')
    {
        $user = Auth::user();

        $share = SharedLink::with('document')
            ->where('id', $shareId)
            ->where('shared_to', $user->id)
            ->active()
            ->first();

        if (!$share || !$share->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan atau akses sudah kedaluwarsa'
            ], 404);
        }

        // Check permission for the action
        if (!$share->hasPermission($action)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk melakukan aksi ini'
            ], 403);
        }

        // Mark as read
        $share->markAsRead();

        $document = $share->document;

        if ($action === 'download') {
            // Check if file exists
            if (!Storage::exists($document->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan'
                ], 404);
            }

            // Return file download
            return Storage::download(
                $document->file_path,
                $document->original_name,
                ['Content-Type' => $document->mime_type]
            );
        }

        // For view action, return document info
        return response()->json([
            'success' => true,
            'document' => [
                'id' => $document->id,
                'name' => $document->name,
                'original_name' => $document->original_name,
                'file_size' => $document->file_size,
                'mime_type' => $document->mime_type
            ],
            'share_info' => [
                'shared_by' => $share->sharedBy->name,
                'message' => $share->message,
                'permissions' => $share->permissions
            ]
        ]);
    }

    /**
     * Revoke share (deactivate)
     */
    public function revokeShare($shareId)
    {
        $user = Auth::user();

        $share = SharedLink::where('id', $shareId)
            ->where('shared_by', $user->id)
            ->first();

        if (!$share) {
            return response()->json([
                'success' => false,
                'message' => 'Share tidak ditemukan'
            ], 404);
        }

        $share->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Share berhasil dicabut'
        ]);
    }

    /**
     * Get users for sharing (search users)
     */
    public function getUsers(Request $request)
    {
        $search = $request->get('search', '');
        $currentUserId = Auth::id();

        $users = User::where('id', '!=', $currentUserId)
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->select('id', 'name', 'email')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get unread shares count (for notification)
     */
    public function getUnreadCount()
    {
        $user = Auth::user();

        $count = SharedLink::where('shared_to', $user->id)
            ->where('is_read', false)
            ->active()
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }

    public function getExistingShares(Document $document)
    {
        $user = Auth::user();

        if (!$this->permissionService->canAccessFolder($user, $document->folder)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk melihat share dokumen ini'
            ], 403);
        }

        $shares = SharedLink::where('document_id', $document->id)
            ->where('shared_by', $user->id)
            ->with(['sharedTo'])
            ->active()
            ->latest()
            ->get()
            ->map(function ($share) {
                return [
                    'id' => $share->id,
                    'shared_to' => [
                        'id' => $share->sharedTo->id,
                        'name' => $share->sharedTo->name,
                        'email' => $share->sharedTo->email
                    ],
                    'permissions' => $share->permissions,
                    'message' => $share->message,
                    'is_read' => $share->is_read,
                    'expires_at' => $share->expires_at?->format('d M Y H:i'),
                    'shared_at' => $share->created_at->format('d M Y H:i')
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $shares
        ]);
    }
}

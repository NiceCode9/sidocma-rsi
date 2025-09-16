<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Folder;
use App\Services\DocumentService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    protected $documentService;
    protected $permissionService;

    public function __construct(DocumentService $documentService, PermissionService $permissionService)
    {
        $this->documentService = $documentService;
        $this->permissionService = $permissionService;
    }

    /**
     * Upload multiple documents
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'folder_id' => 'required|exists:folders,id',
    //         'files.*' => 'required|file|max:10240', // 10MB max per file
    //         'description' => 'nullable|string'
    //     ]);

    //     $folder = Folder::findOrFail($request->folder_id);
    //     $user = Auth::user();

    //     // Check permission
    //     if (!$this->permissionService->canUploadToFolder($user, $folder)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Tidak memiliki izin untuk mengupload ke folder ini'
    //         ], 403);
    //     }

    //     $uploadedFiles = [];
    //     $failedFiles = [];

    //     $isLetter = $request->is_latter ? true : false;

    //     foreach ($request->file('files') as $file) {
    //         try {
    //             $document = $this->documentService->uploadDocument(
    //                 $file,
    //                 $folder,
    //                 $user,
    //                 $request->description,
    //                 $isLetter,
    //             );

    //             $uploadedFiles[] = $document->name;
    //         } catch (\Exception $e) {
    //             $failedFiles[] = [
    //                 'name' => $file->getClientOriginalName(),
    //                 'error' => $e->getMessage()
    //             ];
    //         }
    //     }

    //     $successCount = count($uploadedFiles);
    //     $failedCount = count($failedFiles);

    //     if ($successCount > 0 && $failedCount === 0) {
    //         return response()->json([
    //             'success' => true,
    //             'message' => "{$successCount} file berhasil diupload",
    //             'request' => $request->all()
    //         ]);
    //     } elseif ($successCount > 0 && $failedCount > 0) {
    //         return response()->json([
    //             'success' => true,
    //             'message' => "{$successCount} file berhasil diupload, {$failedCount} file gagal",
    //             'failed_files' => $failedFiles
    //         ]);
    //     } else {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Semua file gagal diupload',
    //             'failed_files' => $failedFiles
    //         ], 422);
    //     }
    // }

    public function store(Request $request)
    {
        // Debug request data
        Log::info('Upload request data:', [
            'folder_id' => $request->input('folder_id'),
            'description' => $request->input('description'),
            'is_latter' => $request->input('is_latter'),
            'files_count' => count($request->file('files', []))
        ]);

        $request->validate([
            'folder_id' => 'required|exists:folders,id',
            'description' => 'nullable|string',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|max:10240', // 10MB max per file
            'is_latter' => 'nullable|boolean'
        ]);

        $folder = Folder::findOrFail($request->folder_id);

        $user = Auth::user();

        // Check if user can write to this folder
        if (!$this->permissionService->hasPermission($user, $folder, 'can_write')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengunggah dokumen ke folder ini'
            ], 403);
        }

        $files = $request->file('files');
        $description = $request->input('description');
        $isLatter = $request->boolean('is_latter'); // Gunakan boolean() helper Laravel
        $uploadedDocuments = [];
        $errors = [];

        foreach ($files as $file) {
            try {
                $document = $this->documentService->uploadDocument(
                    $file,
                    $folder,
                    $user,
                    $description,
                    $isLatter,
                );

                $uploadedDocuments[] = $document;
            } catch (\Exception $e) {
                $failedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'error' => $e->getMessage()
                ];
            }
        }

        if (empty($uploadedDocuments)) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload semua file',
                'errors' => $errors
            ], 500);
        }

        $successCount = count($uploadedDocuments);
        $totalCount = count($files);
        $message = $successCount === $totalCount
            ? "Berhasil mengupload {$successCount} file"
            : "Berhasil mengupload {$successCount} dari {$totalCount} file";

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $uploadedDocuments,
            'errors' => $errors
        ], 201);
    }

    /**
     * Download document
     */
    public function download(Document $document)
    {
        $user = Auth::user();

        // Check permission
        if (!$this->permissionService->canDownloadDocument($user, $document)) {
            return response()->json([
                'error' => 'Tidak memiliki akses untuk mendownload dokumen ini'
            ], 403);
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($document->file_path)) {
            return response()->json([
                'error' => 'File tidak ditemukan'
            ], 404);
        }
        $document->sharedLink->incrementDownload();
        if ($document->sharedLink->read_at == null && !$user->canAccessAllFolders() && !$document->sharedLink->is_read) {
            $document->sharedLink->is_read = true;
            $document->sharedLink->read_at = now();
            $document->sharedLink->opened_by = $user->name;
        }
        $document->sharedLink->save();

        try {
            $filePath = $this->documentService->downloadDocument($document, $user);
            return response()->download($filePath, $document->original_name);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal mendownload file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete document
     */
    public function destroy(Document $document)
    {
        $user = Auth::user();

        // Check permission
        if (!$this->permissionService->canDeleteDocument($user, $document)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki izin untuk menghapus dokumen ini'
            ], 403);
        }

        try {
            $this->documentService->deleteDocument($document, $user);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus dokumen: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Share document
     */
    public function share(Document $document)
    {
        $user = Auth::user();

        if (!$this->permissionService->canAccessFolder($user, $document->folder)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses ke dokumen ini'
            ], 403);
        }

        // Generate share token atau langsung return download link
        $shareUrl = route('documents.download', $document->id);

        return response()->json([
            'success' => true,
            'share_url' => $shareUrl,
            'document' => [
                'id' => $document->id,
                'name' => $document->name,
                'original_name' => $document->original_name
            ]
        ]);
    }

    /**
     * Get document details
     */
    public function show(Document $document)
    {
        $user = Auth::user();

        // Check permission
        if (!$this->permissionService->canAccessDocument($user, $document)) {
            return response()->json([
                'error' => 'Tidak memiliki akses ke dokumen ini'
            ], 403);
        }

        return response()->json([
            'document' => [
                'id' => $document->id,
                'name' => $document->name,
                'original_name' => $document->original_name,
                'description' => $document->description,
                'file_size' => $document->file_size,
                'formatted_size' => $this->formatFileSize($document->file_size),
                'mime_type' => $document->mime_type,
                'extension' => $document->extension,
                'version' => $document->version,
                'created_at' => $document->created_at->format('d M Y H:i'),
                'uploader' => $document->uploader->name,
                'folder' => $document->folder->name
            ]
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

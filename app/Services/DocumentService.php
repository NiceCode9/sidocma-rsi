<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Folder;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    /**
     * Upload document baru
     */
    public function uploadDocument(
        UploadedFile $file,
        Folder $folder,
        User $uploader,
        ?string $description = null
    ): Document {
        $fileName = $this->generateUniqueFileName($file);
        $filePath = $file->storeAs('documents', $fileName, 'public');

        $document = Document::create([
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'folder_id' => $folder->id,
            'uploaded_by' => $uploader->id,
            'description' => $description,
            'version' => 1
        ]);

        // Log activity
        $this->logActivity($uploader, ActivityLog::ACTION_UPLOAD, $document);

        return $document;
    }

    /**
     * Upload new version of document
     */
    public function uploadNewVersion(
        Document $document,
        UploadedFile $file,
        User $uploader,
        ?string $changesDescription = null
    ): DocumentVersion {
        $fileName = $this->generateUniqueFileName($file);
        $filePath = $file->storeAs('documents/versions', $fileName, 'public');

        $newVersion = $document->version + 1;

        // Create version record
        $documentVersion = DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => $newVersion,
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'uploaded_by' => $uploader->id,
            'changes_description' => $changesDescription
        ]);

        // Update document with new version info
        $document->update([
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'version' => $newVersion
        ]);

        // Log activity
        $this->logActivity($uploader, ActivityLog::ACTION_UPDATE, $document);

        return $documentVersion;
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);

        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Delete document
     */
    public function deleteDocument(Document $document, User $deleter): bool
    {
        // Soft delete
        $document->update(['is_active' => false]);

        // Log activity
        $this->logActivity($deleter, ActivityLog::ACTION_DELETE, $document);

        return true;
    }

    /**
     * Download document
     */
    public function downloadDocument(Document $document, User $downloader): string
    {
        // Log activity
        $this->logActivity($downloader, ActivityLog::ACTION_DOWNLOAD, $document);

        return Storage::disk('public')->path($document->file_path);
    }

    /**
     * Log activity
     */
    private function logActivity(User $user, string $action, Document $document): void
    {
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'target_type' => 'document',
            'target_id' => $document->id,
            'target_name' => $document->name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Create shared link untuk document
     */
    public function createSharedLink(
        Document $document,
        User $creator,
        ?string $password = null,
        ?\DateTime $expiresAt = null,
        ?int $maxDownloads = null
    ): \App\Models\SharedLink {
        return \App\Models\SharedLink::create([
            'document_id' => $document->id,
            'created_by' => $creator->id,
            'password' => $password ? bcrypt($password) : null,
            'expires_at' => $expiresAt,
            'max_downloads' => $maxDownloads
        ]);
    }

    /**
     * Get document statistics
     */
    public function getDocumentStats(?User $user = null): array
    {
        $query = Document::active();

        if ($user && !$user->canAccessAllFolders()) {
            $permissionService = new PermissionService();
            $accessibleFolderIds = $permissionService->getAccessibleFolders($user)->pluck('id');
            $query->whereIn('folder_id', $accessibleFolderIds);
        }

        $totalDocuments = $query->count();
        $totalSize = $query->sum('file_size');

        // Get most common file types
        $fileTypes = $query->select('extension')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('extension')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Get recent uploads
        $recentUploads = $query->with(['uploader', 'folder'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return [
            'total_documents' => $totalDocuments,
            'total_size' => $totalSize,
            'formatted_total_size' => $this->formatFileSize($totalSize),
            'file_types' => $fileTypes,
            'recent_uploads' => $recentUploads
        ];
    }

    /**
     * Format file size
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < 4; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

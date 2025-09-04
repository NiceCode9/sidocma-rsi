<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Management System</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.7.2/css/all.min.css" rel="stylesheet">
    <style>
        .folder-item {
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 15px;
            margin: 5px;
            border: 1px solid #e3e6f0;
        }

        .folder-item:hover {
            background-color: #f8f9fc;
            border-color: #5a5c69;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .file-item {
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 10px;
            margin: 3px;
            border: 1px solid #e3e6f0;
        }

        .file-item:hover {
            background-color: #f8f9fc;
            border-color: #5a5c69;
        }

        .breadcrumb {
            background-color: #f8f9fc;
            border: 1px solid #e3e6f0;
        }

        .folder-icon {
            font-size: 3rem;
            color: #ffc107;
        }

        .file-icon {
            font-size: 1.5rem;
            color: #6c757d;
        }

        .folder-name {
            font-weight: 600;
            color: #5a5c69;
            margin-top: 10px;
        }

        .folder-info {
            font-size: 0.8rem;
            color: #858796;
        }

        .toolbar {
            background-color: #fff;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #858796;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
        }
    </style>
</head>

<body style="background-color: #f8f9fc;">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-folder-open mr-2"></i>
                Manajemen Dokumen
            </h1>
            <div>
                <button class="btn btn-primary mr-2" onclick="showCreateFolderModal()">
                    <i class="fas fa-folder-plus mr-1"></i>
                    Buat Folder
                </button>
                <button class="btn btn-success" onclick="showUploadModal()">
                    <i class="fas fa-upload mr-1"></i>
                    Upload File
                </button>
            </div>
        </div>

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb" id="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="#" onclick="navigateToFolder(null)">
                        <i class="fas fa-home mr-1"></i>Root
                    </a>
                </li>
            </ol>
        </nav>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" placeholder="Cari folder atau file..."
                            id="searchInput">
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary active" onclick="setViewMode('grid')">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="setViewMode('list')">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="row" id="contentArea">
            <!-- Folders will be loaded here -->
        </div>

        <!-- Loading Spinner -->
        <div class="text-center py-5" id="loadingSpinner" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Memuat data...</p>
        </div>

        <!-- Empty State -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <i class="fas fa-folder-open"></i>
            <h4>Folder Kosong</h4>
            <p>Belum ada folder atau file di dalam direktori ini.</p>
            <button class="btn btn-primary" onclick="showCreateFolderModal()">
                <i class="fas fa-folder-plus mr-1"></i>
                Buat Folder Pertama
            </button>
        </div>
    </div>

    <!-- Modal Create Folder -->
    <div class="modal fade" id="createFolderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Folder Baru</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="createFolderForm">
                        <div class="form-group">
                            <label>Nama Folder</label>
                            <input type="text" class="form-control" id="folderName" required>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi (Opsional)</label>
                            <textarea class="form-control" id="folderDescription" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Unit</label>
                            <select class="form-control" id="folderUnit">
                                <option value="">-- Pilih Unit --</option>
                                <option value="1">Unit IT</option>
                                <option value="2">Unit HR</option>
                                <option value="3">Unit Keuangan</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="createFolder()">Buat Folder</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Upload File -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload File</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm">
                        <div class="form-group">
                            <label>Pilih File</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="fileInput" multiple>
                                <label class="custom-file-label" for="fileInput">Pilih file...</label>
                            </div>
                            <small class="form-text text-muted">Maksimal ukuran file 10MB per file</small>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi (Opsional)</label>
                            <textarea class="form-control" id="fileDescription" rows="3"></textarea>
                        </div>
                    </form>
                    <div id="uploadProgress" style="display: none;">
                        <div class="progress mb-2">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <div class="text-center">
                            <small id="uploadStatus">Uploading...</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" onclick="uploadFiles()">Upload</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
        let currentFolderId = null;
        let currentPath = [];
        let viewMode = 'grid';

        // Sample data - replace with actual API calls
        const sampleData = {
            folders: [{
                    id: 1,
                    name: 'Unit IT',
                    description: 'Dokumen Unit IT',
                    parent_id: null,
                    documents_count: 5,
                    subfolders_count: 3,
                    created_at: '2024-01-15',
                    unit: {
                        name: 'IT'
                    }
                },
                {
                    id: 2,
                    name: 'Unit HR',
                    description: 'Dokumen Unit HR',
                    parent_id: null,
                    documents_count: 8,
                    subfolders_count: 2,
                    created_at: '2024-01-10',
                    unit: {
                        name: 'HR'
                    }
                },
                {
                    id: 3,
                    name: 'Unit Keuangan',
                    description: 'Dokumen Unit Keuangan',
                    parent_id: null,
                    documents_count: 12,
                    subfolders_count: 4,
                    created_at: '2024-01-08',
                    unit: {
                        name: 'Keuangan'
                    }
                },
                {
                    id: 4,
                    name: 'Proyek Alpha',
                    description: 'Dokumen proyek Alpha',
                    parent_id: 1,
                    documents_count: 3,
                    subfolders_count: 1,
                    created_at: '2024-01-20',
                    unit: {
                        name: 'IT'
                    }
                },
                {
                    id: 5,
                    name: 'Backup Database',
                    description: 'File backup database',
                    parent_id: 1,
                    documents_count: 10,
                    subfolders_count: 0,
                    created_at: '2024-01-18',
                    unit: {
                        name: 'IT'
                    }
                }
            ],
            documents: [{
                    id: 1,
                    name: 'Laporan Bulanan',
                    original_name: 'laporan_januari_2024.pdf',
                    folder_id: 1,
                    file_size: 2048000,
                    extension: 'pdf',
                    created_at: '2024-01-25'
                },
                {
                    id: 2,
                    name: 'Dokumentasi API',
                    original_name: 'api_documentation.docx',
                    folder_id: 1,
                    file_size: 512000,
                    extension: 'docx',
                    created_at: '2024-01-24'
                }
            ]
        };

        $(document).ready(function() {
            loadFolderContent(null);

            $('#searchInput').on('keyup', function() {
                filterContent($(this).val());
            });

            $('#fileInput').on('change', function() {
                const files = Array.from(this.files);
                if (files.length > 0) {
                    $('.custom-file-label').text(`${files.length} file(s) selected`);
                }
            });
        });

        function loadFolderContent(folderId) {
            showLoading();
            currentFolderId = folderId;

            // Simulate API call delay
            setTimeout(() => {
                const folders = sampleData.folders.filter(f => f.parent_id === folderId);
                const documents = folderId ? sampleData.documents.filter(d => d.folder_id === folderId) : [];

                updateBreadcrumb(folderId);
                renderContent(folders, documents);
                hideLoading();
            }, 500);
        }

        function updateBreadcrumb(folderId) {
            const breadcrumb = $('#breadcrumb');
            breadcrumb.html(`
                <li class="breadcrumb-item">
                    <a href="#" onclick="navigateToFolder(null)">
                        <i class="fas fa-home mr-1"></i>Root
                    </a>
                </li>
            `);

            if (folderId) {
                const folder = sampleData.folders.find(f => f.id === folderId);
                if (folder) {
                    breadcrumb.append(`
                        <li class="breadcrumb-item active">
                            <i class="fas fa-folder mr-1"></i>${folder.name}
                        </li>
                    `);
                }
            }
        }

        function renderContent(folders, documents) {
            const contentArea = $('#contentArea');
            const emptyState = $('#emptyState');

            if (folders.length === 0 && documents.length === 0) {
                contentArea.hide();
                emptyState.show();
                return;
            }

            contentArea.show();
            emptyState.hide();
            contentArea.html('');

            // Render folders
            folders.forEach(folder => {
                const folderHtml = viewMode === 'grid' ? renderFolderGrid(folder) : renderFolderList(folder);
                contentArea.append(folderHtml);
            });

            // Render documents
            documents.forEach(document => {
                const documentHtml = viewMode === 'grid' ? renderDocumentGrid(document) : renderDocumentList(
                    document);
                contentArea.append(documentHtml);
            });
        }

        function renderFolderGrid(folder) {
            return `
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <div class="folder-item text-center" onclick="navigateToFolder(${folder.id})">
                        <i class="fas fa-folder folder-icon"></i>
                        <div class="folder-name">${folder.name}</div>
                        <div class="folder-info">
                            <small>
                                <i class="fas fa-folder mr-1"></i>${folder.subfolders_count} folder
                                <br>
                                <i class="fas fa-file mr-1"></i>${folder.documents_count} file
                            </small>
                        </div>
                        <div class="folder-info mt-1">
                            <small class="text-muted">${formatDate(folder.created_at)}</small>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderFolderList(folder) {
            return `
                <div class="col-12">
                    <div class="folder-item d-flex align-items-center" onclick="navigateToFolder(${folder.id})">
                        <i class="fas fa-folder text-warning mr-3" style="font-size: 1.5rem;"></i>
                        <div class="flex-grow-1">
                            <div class="folder-name mb-1">${folder.name}</div>
                            <div class="folder-info">
                                <small class="text-muted">${folder.description || 'Tidak ada deskripsi'}</small>
                            </div>
                        </div>
                        <div class="text-right">
                            <small class="text-muted d-block">${folder.subfolders_count} folder, ${folder.documents_count} file</small>
                            <small class="text-muted">${formatDate(folder.created_at)}</small>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderDocumentGrid(document) {
            const icon = getFileIcon(document.extension);
            return `
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <div class="file-item text-center" onclick="downloadDocument(${document.id})">
                        <i class="${icon} file-icon" style="font-size: 2rem;"></i>
                        <div class="folder-name" style="font-size: 0.9rem;">${document.name}</div>
                        <div class="folder-info">
                            <small class="text-muted">
                                ${formatFileSize(document.file_size)}
                                <br>
                                ${formatDate(document.created_at)}
                            </small>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderDocumentList(document) {
            const icon = getFileIcon(document.extension);
            return `
                <div class="col-12">
                    <div class="file-item d-flex align-items-center" onclick="downloadDocument(${document.id})">
                        <i class="${icon} mr-3" style="font-size: 1.5rem;"></i>
                        <div class="flex-grow-1">
                            <div class="folder-name mb-1" style="font-weight: 500;">${document.name}</div>
                            <div class="folder-info">
                                <small class="text-muted">${document.original_name}</small>
                            </div>
                        </div>
                        <div class="text-right">
                            <small class="text-muted d-block">${formatFileSize(document.file_size)}</small>
                            <small class="text-muted">${formatDate(document.created_at)}</small>
                        </div>
                    </div>
                </div>
            `;
        }

        function navigateToFolder(folderId) {
            loadFolderContent(folderId);
        }

        function setViewMode(mode) {
            viewMode = mode;
            $('.btn-group button').removeClass('active');
            $(`.btn-group button[onclick="setViewMode('${mode}')"]`).addClass('active');

            const folders = sampleData.folders.filter(f => f.parent_id === currentFolderId);
            const documents = currentFolderId ? sampleData.documents.filter(d => d.folder_id === currentFolderId) : [];
            renderContent(folders, documents);
        }

        function showCreateFolderModal() {
            $('#createFolderModal').modal('show');
        }

        function showUploadModal() {
            $('#uploadModal').modal('show');
        }

        function createFolder() {
            const name = $('#folderName').val();
            const description = $('#folderDescription').val();
            const unitId = $('#folderUnit').val();

            if (!name.trim()) {
                alert('Nama folder harus diisi!');
                return;
            }

            // Simulate API call
            console.log('Creating folder:', {
                name,
                description,
                unitId,
                parent_id: currentFolderId
            });

            $('#createFolderModal').modal('hide');
            $('#createFolderForm')[0].reset();

            // Refresh content
            setTimeout(() => {
                loadFolderContent(currentFolderId);
            }, 500);
        }

        function uploadFiles() {
            const files = $('#fileInput')[0].files;
            const description = $('#fileDescription').val();

            if (files.length === 0) {
                alert('Pilih minimal satu file!');
                return;
            }

            $('#uploadProgress').show();
            $('.modal-footer button').prop('disabled', true);

            // Simulate upload progress
            let progress = 0;
            const interval = setInterval(() => {
                progress += 10;
                $('.progress-bar').css('width', progress + '%');

                if (progress >= 100) {
                    clearInterval(interval);
                    $('#uploadModal').modal('hide');
                    $('#uploadForm')[0].reset();
                    $('.custom-file-label').text('Pilih file...');
                    $('#uploadProgress').hide();
                    $('.modal-footer button').prop('disabled', false);
                    $('.progress-bar').css('width', '0%');

                    // Refresh content
                    loadFolderContent(currentFolderId);
                }
            }, 200);
        }

        function downloadDocument(documentId) {
            console.log('Downloading document:', documentId);
            // Implement download logic
        }

        function filterContent(searchTerm) {
            if (!searchTerm.trim()) {
                loadFolderContent(currentFolderId);
                return;
            }

            const folders = sampleData.folders.filter(f =>
                f.parent_id === currentFolderId &&
                f.name.toLowerCase().includes(searchTerm.toLowerCase())
            );

            const documents = currentFolderId ?
                sampleData.documents.filter(d =>
                    d.folder_id === currentFolderId &&
                    d.name.toLowerCase().includes(searchTerm.toLowerCase())
                ) : [];

            renderContent(folders, documents);
        }

        function showLoading() {
            $('#loadingSpinner').show();
            $('#contentArea').hide();
            $('#emptyState').hide();
        }

        function hideLoading() {
            $('#loadingSpinner').hide();
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID');
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        function getFileIcon(extension) {
            const iconMap = {
                'pdf': 'fas fa-file-pdf text-danger',
                'doc': 'fas fa-file-word text-primary',
                'docx': 'fas fa-file-word text-primary',
                'xls': 'fas fa-file-excel text-success',
                'xlsx': 'fas fa-file-excel text-success',
                'ppt': 'fas fa-file-powerpoint text-warning',
                'pptx': 'fas fa-file-powerpoint text-warning',
                'jpg': 'fas fa-file-image text-info',
                'jpeg': 'fas fa-file-image text-info',
                'png': 'fas fa-file-image text-info',
                'gif': 'fas fa-file-image text-info',
                'zip': 'fas fa-file-archive text-secondary',
                'rar': 'fas fa-file-archive text-secondary',
                'txt': 'fas fa-file-alt text-secondary',
                'default': 'fas fa-file text-secondary'
            };

            return iconMap[extension.toLowerCase()] || iconMap['default'];
        }
    </script>
</body>

</html>

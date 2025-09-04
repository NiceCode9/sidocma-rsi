@extends('layouts.app')

@section('title', 'Manajemen Dokumen')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Manajemen Dokumen</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Dokumen</div>
        </div>
    </div>

    <div class="section-body">
        <h2 class="section-title">Daftar Dokumen</h2>
        <p class="section-lead">Kelola semua dokumen yang tersedia dalam sistem.</p>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Dokumen</h4>
                        <div class="card-header-action">
                            <button class="btn btn-primary" id="btn-upload-document">
                                <i class="fas fa-upload"></i> Upload Dokumen
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="documents-table">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Folder</th>
                                        <th>Tipe</th>
                                        <th>Ukuran</th>
                                        <th>Diupload Oleh</th>
                                        <th>Tanggal Upload</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data akan diisi melalui AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Upload Dokumen -->
<div class="modal fade" id="upload-document-modal" tabindex="-1" role="dialog" aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadDocumentModalLabel">Upload Dokumen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="upload-document-form" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="folder_id">Folder</label>
                        <select class="form-control select2" id="folder_id" name="folder_id" required>
                            <option value="">Pilih Folder</option>
                            <!-- Folder options will be loaded via AJAX -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="document_name">Nama Dokumen</label>
                        <input type="text" class="form-control" id="document_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="document_description">Deskripsi</label>
                        <textarea class="form-control" id="document_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="document_file">File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="document_file" name="file" required>
                            <label class="custom-file-label" for="document_file">Pilih file</label>
                        </div>
                        <small class="form-text text-muted">Ukuran maksimum file: 10MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Dokumen -->
<div class="modal fade" id="document-detail-modal" tabindex="-1" role="dialog" aria-labelledby="documentDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentDetailModalLabel">Detail Dokumen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="document-info">
                    <div class="text-center mb-4">
                        <i class="document-icon fas fa-file fa-4x mb-3"></i>
                        <h4 class="document-name">Nama Dokumen</h4>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <th>Folder</th>
                                    <td class="document-folder">-</td>
                                </tr>
                                <tr>
                                    <th>Ukuran</th>
                                    <td class="document-size">-</td>
                                </tr>
                                <tr>
                                    <th>Tipe</th>
                                    <td class="document-type">-</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <th>Diupload Oleh</th>
                                    <td class="document-uploader">-</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Upload</th>
                                    <td class="document-date">-</td>
                                </tr>
                                <tr>
                                    <th>Versi</th>
                                    <td class="document-version">-</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h6>Deskripsi:</h6>
                        <p class="document-description">-</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <a href="#" class="btn btn-primary document-download" target="_blank">
                    <i class="fas fa-download"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="delete-document-modal" tabindex="-1" role="dialog" aria-labelledby="deleteDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteDocumentModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus dokumen <strong id="delete-document-name"></strong>?</p>
                <p class="text-danger">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-document">Hapus</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        const documentsTable = $('#documents-table').DataTable({
            processing: true,
            serverSide: false, // We'll handle the data manually via AJAX
            responsive: true,
            columns: [
                { data: 'name' },
                { data: 'folder' },
                { data: 'type' },
                { data: 'size' },
                { data: 'uploader' },
                { data: 'date' },
                { data: 'actions', orderable: false, searchable: false }
            ]
        });

        // Load folders for select dropdown
        function loadFolders() {
            $.ajax({
                url: '{{ route("folders.tree") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const folderSelect = $('#folder_id');
                    folderSelect.empty().append('<option value="">Pilih Folder</option>');
                    
                    function addFolderOptions(folders, level = 0) {
                        folders.forEach(folder => {
                            const indent = '&nbsp;'.repeat(level * 4);
                            folderSelect.append(`<option value="${folder.id}">${indent}${level > 0 ? '└─ ' : ''}${folder.name}</option>`);
                            
                            if (folder.children && folder.children.length > 0) {
                                addFolderOptions(folder.children, level + 1);
                            }
                        });
                    }
                    
                    addFolderOptions(response);
                    folderSelect.select2({
                        placeholder: 'Pilih Folder',
                        escapeMarkup: function(markup) {
                            return markup;
                        }
                    });
                },
                error: function(xhr) {
                    console.error('Error loading folders:', xhr);
                    Swal.fire('Error', 'Gagal memuat daftar folder', 'error');
                }
            });
        }

        // Load documents
        function loadDocuments() {
            $.ajax({
                url: '{{ route("documents.index") }}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    documentsTable.clear();
                    
                    response.forEach(doc => {
                        const fileIcon = getFileIcon(doc.mime_type, doc.extension);
                        const actions = `
                            <div class="btn-group">
                                <a href="{{ route('documents.download', '') }}/${doc.id}" class="btn btn-sm btn-info" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-primary btn-document-detail" data-id="${doc.id}" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-delete-document" data-id="${doc.id}" data-name="${doc.name}" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                        
                        documentsTable.row.add({
                            name: `<div class="d-flex align-items-center">
                                    <i class="${fileIcon} mr-2"></i>
                                    <span>${doc.name}</span>
                                  </div>`,
                            folder: doc.folder ? doc.folder.name : '-',
                            type: doc.mime_type,
                            size: doc.formatted_file_size || formatFileSize(doc.file_size),
                            uploader: doc.uploader ? doc.uploader.name : '-',
                            date: moment(doc.created_at).format('DD MMM YYYY HH:mm'),
                            actions: actions
                        });
                    });
                    
                    documentsTable.draw();
                },
                error: function(xhr) {
                    console.error('Error loading documents:', xhr);
                    Swal.fire('Error', 'Gagal memuat daftar dokumen', 'error');
                }
            });
        }

        // Get file icon based on mime type and extension
        function getFileIcon(mimeType, extension) {
            // Document types
            if (['doc', 'docx'].includes(extension) || mimeType.includes('word')) {
                return 'far fa-file-word';
            }
            
            // Spreadsheet types
            if (['xls', 'xlsx'].includes(extension) || mimeType.includes('excel') || mimeType.includes('spreadsheet')) {
                return 'far fa-file-excel';
            }
            
            // Presentation types
            if (['ppt', 'pptx'].includes(extension) || mimeType.includes('powerpoint') || mimeType.includes('presentation')) {
                return 'far fa-file-powerpoint';
            }
            
            // PDF
            if (extension === 'pdf' || mimeType === 'application/pdf') {
                return 'far fa-file-pdf';
            }
            
            // Images
            if (mimeType.startsWith('image/')) {
                return 'far fa-file-image';
            }
            
            // Audio
            if (mimeType.startsWith('audio/')) {
                return 'far fa-file-audio';
            }
            
            // Video
            if (mimeType.startsWith('video/')) {
                return 'far fa-file-video';
            }
            
            // Archives
            if (['zip', 'rar', 'tar', 'gz', '7z'].includes(extension) || mimeType.includes('archive') || mimeType.includes('zip')) {
                return 'far fa-file-archive';
            }
            
            // Code
            if (['html', 'css', 'js', 'php', 'py', 'java', 'c', 'cpp', 'h', 'rb', 'json', 'xml'].includes(extension)) {
                return 'far fa-file-code';
            }
            
            // Text
            if (mimeType.startsWith('text/') || ['txt', 'md', 'rtf'].includes(extension)) {
                return 'far fa-file-alt';
            }
            
            // Default
            return 'far fa-file';
        }

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            
            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(1024));
            
            return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + ' ' + units[i];
        }

        // Initialize
        loadFolders();
        loadDocuments();

        // Show upload document modal
        $('#btn-upload-document').on('click', function() {
            $('#upload-document-form')[0].reset();
            $('.custom-file-label').text('Pilih file');
            $('#upload-document-modal').modal('show');
        });

        // Handle file input change
        $('#document_file').on('change', function() {
            const fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').text(fileName);
            
            // Auto-fill document name if empty
            if (!$('#document_name').val()) {
                const nameWithoutExt = fileName.split('.').slice(0, -1).join('.');
                $('#document_name').val(nameWithoutExt);
            }
        });

        // Handle document upload
        $('#upload-document-form').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            $.ajax({
                url: '{{ route("documents.store") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    Swal.fire({
                        title: 'Uploading...',
                        text: 'Mohon tunggu sementara dokumen diupload',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    Swal.fire('Sukses', 'Dokumen berhasil diupload', 'success');
                    $('#upload-document-modal').modal('hide');
                    loadDocuments();
                },
                error: function(xhr) {
                    console.error('Error uploading document:', xhr);
                    let errorMessage = 'Gagal mengupload dokumen';
                    
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors)[0][0];
                    }
                    
                    Swal.fire('Error', errorMessage, 'error');
                }
            });
        });

        // Show document detail
        $(document).on('click', '.btn-document-detail', function() {
            const documentId = $(this).data('id');
            
            $.ajax({
                url: `{{ route('documents.show', '') }}/${documentId}`,
                type: 'GET',
                dataType: 'json',
                success: function(doc) {
                    const fileIcon = getFileIcon(doc.mime_type, doc.extension);
                    
                    $('.document-icon').removeClass().addClass(`document-icon ${fileIcon} fa-4x mb-3`);
                    $('.document-name').text(doc.name);
                    $('.document-folder').text(doc.folder ? doc.folder.name : '-');
                    $('.document-size').text(doc.formatted_file_size || formatFileSize(doc.file_size));
                    $('.document-type').text(doc.mime_type);
                    $('.document-uploader').text(doc.uploader ? doc.uploader.name : '-');
                    $('.document-date').text(moment(doc.created_at).format('DD MMM YYYY HH:mm'));
                    $('.document-version').text(doc.version || '1.0');
                    $('.document-description').text(doc.description || '-');
                    $('.document-download').attr('href', `{{ route('documents.download', '') }}/${doc.id}`);
                    
                    $('#document-detail-modal').modal('show');
                },
                error: function(xhr) {
                    console.error('Error loading document details:', xhr);
                    Swal.fire('Error', 'Gagal memuat detail dokumen', 'error');
                }
            });
        });

        // Show delete confirmation
        $(document).on('click', '.btn-delete-document', function() {
            const documentId = $(this).data('id');
            const documentName = $(this).data('name');
            
            $('#delete-document-name').text(documentName);
            $('#confirm-delete-document').data('id', documentId);
            $('#delete-document-modal').modal('show');
        });

        // Handle document deletion
        $('#confirm-delete-document').on('click', function() {
            const documentId = $(this).data('id');
            
            $.ajax({
                url: `{{ route('documents.destroy', '') }}/${documentId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    $('#delete-document-modal').modal('hide');
                    Swal.fire('Sukses', 'Dokumen berhasil dihapus', 'success');
                    loadDocuments();
                },
                error: function(xhr) {
                    console.error('Error deleting document:', xhr);
                    let errorMessage = 'Gagal menghapus dokumen';
                    
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    
                    $('#delete-document-modal').modal('hide');
                    Swal.fire('Error', errorMessage, 'error');
                }
            });
        });
    });
</script>
@endsection
{{-- Add this modal to your browse.blade.php --}}

<!-- Modal Internal Share Document -->
<div class="modal fade" id="internalShareModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-users mr-2"></i>Share Document
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Document Info -->
                <div class="alert alert-info">
                    <i class="fas fa-file mr-2"></i>
                    <strong id="shareDocumentName">Document Name</strong>
                </div>

                <!-- Share Form -->
                <form id="internalShareForm">
                    <!-- User Selection -->
                    <div class="form-group">
                        <label>Share dengan User <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="userSearch"
                                placeholder="Cari nama atau email user...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" onclick="searchUsers()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Search Results -->
                        <div id="userSearchResults" class="mt-2"
                            style="max-height: 200px; overflow-y: auto; display: none;"></div>
                        <!-- Selected Users -->
                        <div id="selectedUsers" class="mt-2"></div>
                    </div>

                    <!-- Permissions -->
                    <div class="form-group">
                        <label>Permissions <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="permView" value="view"
                                        checked>
                                    <label class="custom-control-label" for="permView">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="permDownload"
                                        value="download">
                                    <label class="custom-control-label" for="permDownload">
                                        <i class="fas fa-download mr-1"></i>Download
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="permShare" value="share">
                                    <label class="custom-control-label" for="permShare">
                                        <i class="fas fa-share mr-1"></i>Re-share
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message -->
                    <div class="form-group">
                        <label>Pesan (Optional)</label>
                        <textarea class="form-control" id="shareMessage" rows="3" placeholder="Tambahkan pesan untuk penerima..."></textarea>
                    </div>

                    <!-- Expiry -->
                    <div class="form-group">
                        <label>Masa Berlaku</label>
                        <select class="form-control" id="shareExpiry">
                            <option value="">Tidak pernah kedaluwarsa</option>
                            <option value="1">1 Hari</option>
                            <option value="7">7 Hari</option>
                            <option value="30">30 Hari</option>
                            <option value="90">90 Hari</option>
                        </select>
                    </div>
                </form>

                <!-- Existing Shares -->
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Shared With</h6>
                    <button class="btn btn-sm btn-outline-secondary" onclick="loadExistingShares()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div id="existingShares">
                    <div class="text-center text-muted">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="createInternalShare()">
                    <i class="fas fa-share mr-1"></i>Share Document
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Shared Documents Menu (Add to main navigation) -->
<div class="modal fade" id="sharedDocumentsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-share-alt mr-2"></i>Shared Documents
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs" id="sharedDocsTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="shared-with-me-tab" data-toggle="tab" href="#shared-with-me"
                            role="tab">
                            <i class="fas fa-inbox mr-1"></i>
                            Shared with Me
                            <span class="badge badge-primary ml-1" id="unreadCount" style="display: none;"></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="shared-by-me-tab" data-toggle="tab" href="#shared-by-me"
                            role="tab">
                            <i class="fas fa-share mr-1"></i>
                            Shared by Me
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="sharedDocsTabContent">
                    <!-- Shared with Me -->
                    <div class="tab-pane fade show active" id="shared-with-me" role="tabpanel">
                        <div id="sharedWithMeContent" class="mt-3">
                            <div class="text-center text-muted">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </div>
                        </div>
                    </div>

                    <!-- Shared by Me -->
                    <div class="tab-pane fade" id="shared-by-me" role="tabpanel">
                        <div id="sharedByMeContent" class="mt-3">
                            <div class="text-center text-muted">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

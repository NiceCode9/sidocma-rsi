{{-- resources/views/admin/folders/partials/permissions.blade.php --}}

<div class="permission-management">
    <!-- Folder Info Header -->
    <div class="alert alert-info">
        <div class="d-flex align-items-center">
            <i class="fas fa-folder text-primary mr-2"></i>
            <div>
                <h6 class="mb-0">Kelola Permission: <strong>{{ $folder->name }}</strong></h6>
                <small class="text-muted">
                    Path: {{ $folder->path }} •
                    Unit: {{ $folder->unit ? $folder->unit->name : 'Semua Unit' }}
                </small>
            </div>
        </div>
    </div>

    <!-- Permission Tabs -->
    <ul class="nav nav-tabs" id="permissionTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="unit-tab" data-toggle="tab" href="#unit-permissions" role="tab">
                <i class="fas fa-building"></i> Unit & Role
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="user-tab" data-toggle="tab" href="#user-permissions" role="tab">
                <i class="fas fa-user"></i> User Spesifik
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="preview-tab" data-toggle="tab" href="#access-preview" role="tab">
                <i class="fas fa-eye"></i> Preview Akses
            </a>
        </li>
    </ul>

    <div class="tab-content mt-3" id="permissionTabsContent">
        <!-- Unit & Role Permissions Tab -->
        <div class="tab-pane fade show active" id="unit-permissions" role="tabpanel">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6>Tambah Permission Unit/Role</h6>
                        </div>
                        <div class="card-body">
                            <form id="unitPermissionForm">
                                <div class="form-group">
                                    <label>Unit <span class="text-danger">*</span></label>
                                    <select class="form-control" name="unit_id" required>
                                        <option value="">Pilih Unit</option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Role (Opsional)</label>
                                    <select class="form-control" name="role_id">
                                        <option value="">Semua Role dalam Unit</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Kosongkan untuk memberikan akses ke semua role dalam
                                        unit</small>
                                </div>

                                <div class="form-group">
                                    <label>Permission</label>
                                    <div class="permission-checkboxes">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="unit_can_read"
                                                name="can_read" checked>
                                            <label class="custom-control-label" for="unit_can_read">
                                                <i class="fas fa-eye text-info mr-1"></i> Read (Lihat)
                                            </label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="unit_can_write"
                                                name="can_write">
                                            <label class="custom-control-label" for="unit_can_write">
                                                <i class="fas fa-edit text-warning mr-1"></i> Write (Edit & Upload)
                                            </label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="unit_can_delete"
                                                name="can_delete">
                                            <label class="custom-control-label" for="unit_can_delete">
                                                <i class="fas fa-trash text-danger mr-1"></i> Delete (Hapus)
                                            </label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="unit_can_share"
                                                name="can_share">
                                            <label class="custom-control-label" for="unit_can_share">
                                                <i class="fas fa-share text-success mr-1"></i> Share (Berbagi)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Permission
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h6>Permission Unit & Role Saat Ini</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Unit</th>
                                            <th>Role</th>
                                            <th width="60" class="text-center">Read</th>
                                            <th width="60" class="text-center">Write</th>
                                            <th width="60" class="text-center">Delete</th>
                                            <th width="60" class="text-center">Share</th>
                                            <th width="80" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="unitPermissionsList">
                                        @foreach ($folder->permissions->where('user_id', null) as $permission)
                                            <tr data-permission-id="{{ $permission->id }}">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm bg-primary text-white mr-2">
                                                            {{ substr($permission->unit->name ?? 'ALL', 0, 2) }}
                                                        </div>
                                                        {{ $permission->unit->name ?? 'Semua Unit' }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge badge-{{ $permission->role ? 'info' : 'secondary' }}">
                                                        {{ $permission->role->name ?? 'Semua Role' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    {!! $permission->can_read
                                                        ? '<i class="fas fa-check text-success"></i>'
                                                        : '<i class="fas fa-times text-muted"></i>' !!}
                                                </td>
                                                <td class="text-center">
                                                    {!! $permission->can_write
                                                        ? '<i class="fas fa-check text-success"></i>'
                                                        : '<i class="fas fa-times text-muted"></i>' !!}
                                                </td>
                                                <td class="text-center">
                                                    {!! $permission->can_delete
                                                        ? '<i class="fas fa-check text-success"></i>'
                                                        : '<i class="fas fa-times text-muted"></i>' !!}
                                                </td>
                                                <td class="text-center">
                                                    {!! $permission->can_share
                                                        ? '<i class="fas fa-check text-success"></i>'
                                                        : '<i class="fas fa-times text-muted"></i>' !!}
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        onclick="removePermission({{ $permission->id }})"
                                                        title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if ($folder->permissions->where('user_id', null)->isEmpty())
                                            <tr id="noUnitPermissions">
                                                <td colspan="7" class="text-center text-muted">
                                                    <i>Belum ada permission unit/role yang diberikan</i>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Specific Permissions Tab -->
        <div class="tab-pane fade" id="user-permissions" role="tabpanel">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6>Tambah Permission User</h6>
                        </div>
                        <div class="card-body">
                            <form id="userPermissionForm">
                                <div class="form-group">
                                    <label>User <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="user_id" required>
                                        <option value="">Pilih User</option>
                                        @foreach ($users->groupBy('unit.name') as $unitName => $unitUsers)
                                            <optgroup label="{{ $unitName ?? 'No Unit' }}">
                                                @foreach ($unitUsers as $user)
                                                    <option value="{{ $user->id }}">
                                                        {{ $user->name }} ({{ $user->role->name }})
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Permission</label>
                                    <div class="permission-checkboxes">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="user_can_read"
                                                name="can_read" checked>
                                            <label class="custom-control-label" for="user_can_read">
                                                <i class="fas fa-eye text-info mr-1"></i> Read (Lihat)
                                            </label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="user_can_write"
                                                name="can_write">
                                            <label class="custom-control-label" for="user_can_write">
                                                <i class="fas fa-edit text-warning mr-1"></i> Write (Edit & Upload)
                                            </label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="user_can_delete"
                                                name="can_delete">
                                            <label class="custom-control-label" for="user_can_delete">
                                                <i class="fas fa-trash text-danger mr-1"></i> Delete (Hapus)
                                            </label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="user_can_share"
                                                name="can_share">
                                            <label class="custom-control-label" for="user_can_share">
                                                <i class="fas fa-share text-success mr-1"></i> Share (Berbagi)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Permission
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h6>Permission User Spesifik Saat Ini</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Unit</th>
                                            <th width="60" class="text-center">Read</th>
                                            <th width="60" class="text-center">Write</th>
                                            <th width="60" class="text-center">Delete</th>
                                            <th width="60" class="text-center">Share</th>
                                            <th width="80" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="userPermissionsList">
                                        @foreach ($folder->permissions->where('user_id', '!=', null) as $permission)
                                            <tr data-permission-id="{{ $permission->id }}">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm bg-success text-white mr-2">
                                                            {{ substr($permission->user->name, 0, 2) }}
                                                        </div>
                                                        <div>
                                                            {{ $permission->user->name }}<br>
                                                            <small
                                                                class="text-muted">{{ $permission->user->role->name }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        {{ $permission->user->unit->name ?? 'No Unit' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    {!! $permission->can_read
                                                        ? '<i class="fas fa-check text-success"></i>'
                                                        : '<i class="fas fa-times text-muted"></i>' !!}
                                                </td>
                                                <td class="text-center">
                                                    {!! $permission->can_write
                                                        ? '<i class="fas fa-check text-success"></i>'
                                                        : '<i class="fas fa-times text-muted"></i>' !!}
                                                </td>
                                                <td class="text-center">
                                                    {!! $permission->can_delete
                                                        ? '<i class="fas fa-check text-success"></i>'
                                                        : '<i class="fas fa-times text-muted"></i>' !!}
                                                </td>
                                                <td class="text-center">
                                                    {!! $permission->can_share
                                                        ? '<i class="fas fa-check text-success"></i>'
                                                        : '<i class="fas fa-times text-muted"></i>' !!}
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        onclick="removePermission({{ $permission->id }})"
                                                        title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if ($folder->permissions->where('user_id', '!=', null)->isEmpty())
                                            <tr id="noUserPermissions">
                                                <td colspan="7" class="text-center text-muted">
                                                    <i>Belum ada permission user spesifik yang diberikan</i>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Access Preview Tab -->
        <div class="tab-pane fade" id="access-preview" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h6>Preview Akses User ke Folder Ini</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshAccessPreview()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div id="accessPreviewContent">
                        <div class="text-center">
                            <div class="spinner-border spinner-border-sm" role="status"></div>
                            <span class="ml-2">Memuat preview akses...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .permission-checkboxes .custom-control {
        margin-bottom: 10px;
    }

    .avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
    }

    .avatar-sm {
        width: 24px;
        height: 24px;
        font-size: 10px;
    }

    .permission-management .nav-tabs {
        border-bottom: 2px solid #e9ecef;
    }

    .permission-management .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        font-weight: 500;
    }

    .permission-management .nav-tabs .nav-link.active {
        background-color: transparent;
        border-bottom-color: #007bff;
        color: #007bff;
    }

    .table th {
        border-top: none;
        font-weight: 600;
        font-size: 13px;
        color: #495057;
    }

    .table td {
        font-size: 13px;
        vertical-align: middle;
    }
</style>

<script>
    $(document).ready(function() {
        // Initialize select2 for better user selection
        if ($.fn.select2) {
            $('.select2').select2({
                dropdownParent: $('#permissionModal'),
                placeholder: 'Pilih user...',
                allowClear: true
            });
        }

        // Load access preview when tab is clicked
        $('#preview-tab').on('click', function() {
            refreshAccessPreview();
        });

        // Unit permission form submission
        $('#unitPermissionForm').on('submit', function(e) {
            e.preventDefault();
            grantUnitPermission();
        });

        // User permission form submission
        $('#userPermissionForm').on('submit', function(e) {
            e.preventDefault();
            grantUserPermission();
        });
    });

    function grantUnitPermission() {
        const formData = new FormData($('#unitPermissionForm')[0]);
        formData.append('type', 'unit');
        formData.append('target_id', formData.get('unit_id'));

        $.ajax({
            url: `{{ route('folders.permissions.grant', $folder) }}`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function(response) {
            if (response.success) {
                iziToast.success({
                    title: 'Berhasil',
                    message: response.message,
                    position: 'topRight'
                });

                // Reset form and reload permissions
                $('#unitPermissionForm')[0].reset();
                $('#unitPermissionForm input[name="can_read"]').prop('checked', true);
                reloadPermissions();
            }
        }).fail(function(xhr) {
            iziToast.error({
                title: 'Error',
                message: xhr.responseJSON?.message || 'Gagal memberikan permission',
                position: 'topRight'
            });
        });
    }

    function grantUserPermission() {
        const formData = new FormData($('#userPermissionForm')[0]);
        formData.append('type', 'user');
        formData.append('target_id', formData.get('user_id'));

        $.ajax({
            url: `{{ route('folders.permissions.grant', $folder) }}`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function(response) {
            if (response.success) {
                iziToast.success({
                    title: 'Berhasil',
                    message: response.message,
                    position: 'topRight'
                });

                // Reset form and reload permissions
                $('#userPermissionForm')[0].reset();
                $('#userPermissionForm input[name="can_read"]').prop('checked', true);
                if ($('.select2').length) {
                    $('.select2').val(null).trigger('change');
                }
                reloadPermissions();
            }
        }).fail(function(xhr) {
            iziToast.error({
                title: 'Error',
                message: xhr.responseJSON?.message || 'Gagal memberikan permission',
                position: 'topRight'
            });
        });
    }

    function removePermission(permissionId) {
        if (!confirm('Apakah Anda yakin ingin menghapus permission ini?')) {
            return;
        }

        $.ajax({
            url: `{{ route('folders.permissions.revoke', $folder) }}`,
            method: 'DELETE',
            data: {
                permission_id: permissionId
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function(response) {
            if (response.success) {
                iziToast.success({
                    title: 'Berhasil',
                    message: response.message,
                    position: 'topRight'
                });

                // Remove row from table
                $(`tr[data-permission-id="${permissionId}"]`).remove();

                // Show "no permissions" message if tables are empty
                checkEmptyTables();
                refreshAccessPreview();
            }
        }).fail(function(xhr) {
            iziToast.error({
                title: 'Error',
                message: xhr.responseJSON?.message || 'Gagal menghapus permission',
                position: 'topRight'
            });
        });
    }

    function reloadPermissions() {
        // Reload the entire permissions content
        $('#permissionContent').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');

        $.get(`{{ route('folders.permissions', $folder) }}`).done(function(html) {
            $('#permissionContent').html(html);
        }).fail(function() {
            $('#permissionContent').html('<div class="alert alert-danger">Gagal memuat data permission</div>');
        });
    }

    function refreshAccessPreview() {
        $('#accessPreviewContent').html(`
        <div class="text-center">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <span class="ml-2">Memuat preview akses...</span>
        </div>
    `);

        $.get(`{{ route('folders.access-preview', $folder) }}`).done(function(accessList) {
            let html = '';

            if (accessList.length === 0) {
                html =
                    '<div class="alert alert-warning">Tidak ada user yang memiliki akses ke folder ini</div>';
            } else {
                html = '<div class="table-responsive"><table class="table table-sm">';
                html +=
                    '<thead><tr><th>User</th><th>Unit</th><th>Role</th><th width="60" class="text-center">Read</th><th width="60" class="text-center">Write</th><th width="60" class="text-center">Delete</th><th width="60" class="text-center">Share</th></tr></thead><tbody>';

                accessList.forEach(item => {
                    html += '<tr>';
                    html += `<td>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm bg-primary text-white mr-2">${item.user.name.substring(0, 2)}</div>
                        ${item.user.name}
                    </div>
                </td>`;
                    html +=
                        `<td><span class="badge badge-info">${item.user.unit?.name || 'No Unit'}</span></td>`;
                    html +=
                        `<td><span class="badge badge-secondary">${item.user.role.name}</span></td>`;
                    html +=
                        `<td class="text-center">${item.permissions.read ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-muted"></i>'}</td>`;
                    html +=
                        `<td class="text-center">${item.permissions.write ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-muted"></i>'}</td>`;
                    html +=
                        `<td class="text-center">${item.permissions.delete ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-muted"></i>'}</td>`;
                    html +=
                        `<td class="text-center">${item.permissions.share ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-muted"></i>'}</td>`;
                    html += '</tr>';
                });

                html += '</tbody></table></div>';
            }

            $('#accessPreviewContent').html(html);
        }).fail(function() {
            $('#accessPreviewContent').html('<div class="alert alert-danger">Gagal memuat preview akses</div>');
        });
    }

    function checkEmptyTables() {
        // Check if unit permissions table is empty
        if ($('#unitPermissionsList tr[data-permission-id]').length === 0) {
            $('#unitPermissionsList').html(`
            <tr id="noUnitPermissions">
                <td colspan="7" class="text-center text-muted">
                    <i>Belum ada permission unit/role yang diberikan</i>
                </td>
            </tr>
        `);
        }

        // Check if user permissions table is empty
        if ($('#userPermissionsList tr[data-permission-id]').length === 0) {
            $('#userPermissionsList').html(`
            <tr id="noUserPermissions">
                <td colspan="7" class="text-center text-muted">
                    <i>Belum ada permission user spesifik yang diberikan</i>
                </td>
            </tr>
        `);
        }
    }
</script>

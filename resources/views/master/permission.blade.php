@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Permission</h1>
        </div>
        <div class="section-body">
            <h2 class="section-title">Permission</h2>
            <p class="section-lead">
                Halaman ini ditujukan untuk mengelola permission.
            </p>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-header-action">
                                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#permissionModal"
                                    onclick="addPermission()">
                                    <i class="fas fa-plus"></i> Add New Permission
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="permissionTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Guard Name</th>
                                            <th>Created At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($permission as $item)
                                            <tr id="permission_{{ $item->id }}">
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->guard_name }}</td>
                                                <td>{{ $item->created_at }}</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="#" class="btn btn-icon btn-sm btn-warning"
                                                            data-toggle="modal" data-target="#permissionModal"
                                                            onclick="editPermission({{ $item->id }})">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="#" class="btn btn-icon btn-sm btn-danger"
                                                            onclick="deletePermission({{ $item->id }})">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" tabindex="-1" role="dialog" id="permissionModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Permission</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="permissionForm">
                    @csrf
                    <input type="hidden" id="permissionId" name="id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>
                        <div class="form-group">
                            <label for="guard_name">Guard Name</label>
                            <input type="text" class="form-control" id="guard_name" name="guard_name" required>
                            <div class="invalid-feedback" id="guardNameError"></div>
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Set CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Function to reset form and modal
        function resetForm() {
            $('#permissionForm')[0].reset();
            $('#permissionId').val('');
            $('.invalid-feedback').text('');
            $('.form-control').removeClass('is-invalid');
            $('#modalTitle').text('Add New Permission');
        }

        // Function to show validation errors
        function showErrors(errors) {
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            $.each(errors, function(key, value) {
                $('#' + key).addClass('is-invalid');
                $('#' + key + 'Error').text(value[0]);
            });
        }

        // Function to add new permission
        function addPermission() {
            resetForm();
        }

        // Function to edit permission
        function editPermission(id) {
            resetForm();
            $('#modalTitle').text('Edit Permission');

            $.ajax({
                url: "{{ url('permission') }}/" + id,
                type: 'GET',
                success: function(response) {
                    if (response.status) {
                        $('#permissionId').val(response.data.id);
                        $('#name').val(response.data.name);
                        $('#guard_name').val(response.data.guard_name);
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'Terjadi kesalahan saat mengambil data', 'error');
                }
            });
        }

        // Function to delete permission
        function deletePermission(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('permission') }}/" + id,
                        type: 'DELETE',
                        success: function(response) {
                            if (response.status) {
                                $('#permission_' + id).remove();
                                Swal.fire('Deleted!', response.message, 'success');
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', 'Terjadi kesalahan saat menghapus data', 'error');
                        }
                    });
                }
            });
        }

        // Handle form submission
        $('#permissionForm').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serialize();
            var url = "{{ route('permission.store') }}";
            var method = 'POST';

            // If editing, change URL and method
            var permissionId = $('#permissionId').val();
            if (permissionId) {
                url = "{{ url('permission') }}/" + permissionId;
                method = 'PUT';
            }

            $.ajax({
                url: url,
                type: method,
                data: formData,
                success: function(response) {
                    if (response.status) {
                        $('#permissionModal').modal('hide');
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        showErrors(xhr.responseJSON.errors);
                    } else {
                        Swal.fire('Error!', 'Terjadi kesalahan saat menyimpan data', 'error');
                    }
                }
            });
        });
    </script>
@endpush

@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Data User</h1>
        </div>
        <div class="section-body">
            <h2 class="section-title">Management Data User</h2>
            <p class="section-lead">Halaman Untuk management data pengguna</p>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-succes">
                        <div class="card-header">
                            <div class="card-header-action">
                                <a href="javascript:void(0)" class="btn btn-sm btn-primary" data-toggle="modal"
                                    data-target="#modalForm"><i class="fas fa-plus"></i> Add New User</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="table-user">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Username</th>
                                            <th>Unit</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" tabindex="-1" role="dialog" id="modalForm">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Form User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="POST" id="formUser">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Nama User</label>
                            <input type="text" class="form-control" name="name" id="name"
                                placeholder="Masukkan Nama User" required>
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" name="username" id="username"
                                placeholder="Masukkan Username" required>
                        </div>
                        <div class="form-group">
                            <label for="unit_id">Unit</label>
                            <select name="unit_id" id="unit_id" class="form-control">
                                <option value="">-- Pilih Unit --</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select name="role" id="role" class="form-control">
                                <option value="">-- Pilih Role --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="is_active">Is Active</label>
                            <select name="is_active" id="is_active" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let table = $('#table-user').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('users.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'username',
                        name: 'username'
                    },
                    {
                        data: 'unit',
                        name: 'unit'
                    },
                    {
                        data: 'role',
                        name: 'role'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            $('#formUser').on('submit', function(e) {
                e.preventDefault();
                let url = $(this).attr('action');
                let method = $(this).attr('method');
                let formData = new FormData(this);

                if (method === 'PUT') {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    type: 'POST',
                    url: url || '/users',
                    data: formData,
                    dataType: "json",
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                            }).then(() => {
                                table.ajax.reload();
                                $('#modalForm').modal('hide');
                            });
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessage = '';
                        $.each(errors, function(key, value) {
                            errorMessage += value + '\n';
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: errorMessage,
                        });
                    }
                });
            });

            $('#table-user').on('click', '.editBtn', function() {
                let id = $(this).data('id');
                let url = '/users/' + id;
                $.get('/users/' + id + '/edit', function(response) {
                    $('#modalForm').modal('show');
                    $('#formUser').attr('action', url);
                    $('#formUser').attr('method', 'PUT');
                    $('#id').val(response.id);
                    $('#name').val(response.name);
                    $('#username').val(response.username);
                    $('#unit_id').val(response.unit_id);
                    $('#role').val(response.role);
                    $('#is_active').val(response.is_active ? '1' : '0');
                });
            });

            $('#table-user').on('click', '.destroyBtn', function() {
                let id = $(this).data('id');
                let url = '/users/' + id;
                destroy(url, table);
            });

            $('#modal-form').on('hidden.bs.modal', function() {
                $('#formUser').trigger('reset');
                $('#formUser').attr('action', '');
                $('#formUser').attr('method', 'POST');
                $('#id').val('');
            });
        });
    </script>
@endpush

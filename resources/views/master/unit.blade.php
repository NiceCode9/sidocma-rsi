@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Management Data Unit</h1>
        </div>
        <div class="section-body">
            <h2 class="section-title">Data Unit</h2>
            <p class="section-lead">
                Halaman ini ditujukan untuk mengelola data unit.
            </p>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <div class="card-header-action">
                                <a href="#" class="btn btn-primary" data-target="#modal-form" data-toggle="modal">
                                    <i class="fas fa-plus"></i> Add New Unit
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover datatable" id="unit-table" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Code</th>
                                            <th>Description</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($units as $unit)
                                            <tr>
                                                <td>{{ $unit->name }}</td>
                                                <td>{{ $unit->code }}</td>
                                                <td>{{ $unit->description }}</td>
                                                <td>
                                                    <a href="javascript:void(0)" data-id="{{ $unit->id }}"
                                                        class="btn btn-primary btn-sm edit-unit">Edit</a>
                                                    <a href="javascript:void(0)" data-id="{{ $unit->id }}"
                                                        class="btn btn-danger btn-sm delete-unit">Delete</a>
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
        </div>
    </section>

    <div class="modal fade" tabindex="-1" role="dialog" id="modal-form">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Form Unit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="POST" id="formUnit">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Nama Unit</label>
                            <input type="text" class="form-control" name="name" id="name"
                                placeholder="Masukkan Nama Unit" required>
                        </div>
                        <div class="form-group">
                            <label for="code">Kode Unit</label>
                            <input type="text" class="form-control" name="code" id="code"
                                placeholder="Masukkan Kode Unit" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Deskripsi Unit</label>
                            <textarea class="form-control" name="description" id="description" placeholder="Masukkan Deskripsi Unit" required></textarea>
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
            $('#unit-table').DataTable();

            $('#formUnit').on('submit', function(e) {
                e.preventDefault();
                let url = $(this).attr('action');
                let method = $(this).attr('method');
                let formData = new FormData(this);

                if (method === 'PUT') {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    type: "POST",
                    url: url || "/unit",
                    data: formData,
                    dataType: "json",
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log(response);
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessage = '';

                        $.each(errors, function(key, value) {
                            errorMessage += value + '\n';
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON.message,
                        });
                    }
                });
            });

            $('.datatable').on('click', '.edit-unit', function() {
                let id = $(this).data('id');
                let url = '/unit/' + id + '/edit';
                let method = 'GET';

                $('#formUnit').trigger('reset');

                $.ajax({
                    type: "GET",
                    url: url,
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            $('#id').val(response.data.id);
                            $('#name').val(response.data.name);
                            $('#code').val(response.data.code);
                            $('#description').val(response.data.description);
                            $('#formUnit').attr('action', '/unit/' + id);
                            $('#formUnit').attr('method', 'PUT');
                            $('#modal-form').modal('show');
                        }
                    },
                    error: function(response) {
                        let errors = response.responseJSON.errors;
                        let errorMessage = '';

                        $.each(errors, function(key, value) {
                            errorMessage += value + '\n';
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.responseJSON.message,
                        });
                    }
                });
            });

            $('.datatable').on('click', '.delete-unit', function() {
                let id = $(this).data('id');
                let url = '/unit/' + id;
                let method = 'DELETE';

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Anda tidak akan dapat mengembalikan ini!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus saja!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: url,
                            dataType: "json",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire(
                                        'Deleted!',
                                        response.message,
                                        'success'
                                    ).then(() => {
                                        $('#unit-table').DataTable().ajax
                                            .reload();
                                    });
                                }
                            },
                            error: function(response) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.responseJSON.message,
                                });
                            }
                        });
                    }
                });
            });

            $('#modal-form').on('hidden.bs.modal', function() {
                $('#formUnit').trigger('reset');
                $('#formUnit').attr('action', '');
                $('#formUnit').attr('method', 'POST');
                $('#id').val('');
            });
        });
    </script>
@endpush

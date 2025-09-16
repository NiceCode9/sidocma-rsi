@extends('layouts.app', ['title' => 'Arsip Surat'])

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Arsip Surat</h1>
        </div>

        <div class="section-body">
            <h2 class="section-title">Data Surat</h2>
            <p class="section-lead">
                Halaman ini ditujukan untuk mengelola data surat yang sudah di bagikan.
            </p>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-header text-nowrap" id="table-surat">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Judul</th>
                                            <th>Status</th>
                                            <th>Tanggal Dibuka</th>
                                            <th>Dibuka Oleh</th>
                                            <th>Tanggal Dikirim</th>
                                            <th>File</th>
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
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#table-surat').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('arsip-surat.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'judul',
                        name: 'judul'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'read_at',
                        name: 'read_at'
                    },
                    {
                        data: 'opened_by',
                        name: 'opened_by'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'file',
                        name: 'file',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        });
    </script>
@endpush

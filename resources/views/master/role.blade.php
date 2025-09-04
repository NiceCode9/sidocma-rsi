@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Management Role User</h1>
        </div>
        <div class="section-body">
            <h2 class="section-title">Role User</h2>
            <p class="section-lead">
                Halaman ini ditujukan untuk mengelola role user.
            </p>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <div class="card-header-action">
                                <a href="#" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add New Role
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Role</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($roles as $role)
                                            <tr>
                                                <td>{{ $role->name }}</td>
                                                <td>
                                                    <a href="#" class="btn btn-primary btn-sm">Edit</a>
                                                    <a href="#" class="btn btn-danger btn-sm">Delete</a>
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
@endsection

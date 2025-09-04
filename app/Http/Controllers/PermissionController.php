<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permission = Permission::orderBy('name')->get();
        return view('master.permission', compact('permission'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:permissions,name',
            'guard_name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            Permission::create($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Permission berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json([
                'status' => false,
                'message' => 'Data permission tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $permission
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json([
                'status' => false,
                'message' => 'Data permission tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:permissions,name,' . $id,
            'guard_name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $permission->update($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Permission berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json([
                'status' => false,
                'message' => 'Data permission tidak ditemukan'
            ], 404);
        }

        try {
            $permission->delete();

            return response()->json([
                'status' => true,
                'message' => 'Permission berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}

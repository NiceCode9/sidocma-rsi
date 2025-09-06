<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = User::with('unit')->get();

            return DataTables::make($user)
                ->addIndexColumn()
                ->addColumn('unit', function ($row) {
                    return $row->unit ? $row->unit->name : 'N/A';
                })
                ->addColumn('role', function ($row) {
                    return $row->getRoleNames()->map(function ($roles) {
                        return "<span class='badge badge-info'>$roles</span>";
                    })->implode(' ');
                    // return "<span class='badge badge-info'>" . $row->getRoleNames() . "</span>";
                })
                ->editColumn('is_active', function (User $user) {
                    return $user->is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="javascript:void(0)" class="btn btn-sm btn-warning mr-1 editBtn" data-id="' . $row->id . '">Edit</a>';
                    $btn .= '<a href="javascript:void(0)" class="btn btn-sm btn-danger mr-1 destroyBtn" data-id="' . $row->id . '">Hapus</a>';
                    return $btn;
                })
                ->rawColumns(['role', 'is_active', 'action'])
                ->make(true);
        }

        $roles = Role::all();
        $units = \App\Models\Unit::all();

        return view('master.user', compact('roles', 'units'));
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
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'role' => 'required|string',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => bcrypt('password'),
            'unit_id' => $request->unit_id,
            'is_active' => true,
        ]);

        $user->assignRole($request->role);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::with('roles', 'unit')->findOrFail($id);
        $user->role = $user->roles->pluck('name')->first();
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'role' => 'required|string',
            'unit_id' => 'nullable|exists:units,id',
            'is_active' => 'required|boolean',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'unit_id' => $request->unit_id,
            'is_active' => $request->is_active,
        ]);

        $user->syncRoles([$request->role]);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $th->getMessage(),
            ], 500);
        }
    }
}

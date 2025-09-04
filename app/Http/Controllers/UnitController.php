<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::orderBy('name')->get();
        return view('master.unit', compact('units'));
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
            'name' => 'required',
            'code' => 'required|unique:units,code',
            'description' => 'required',
        ]);
        Unit::create($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Data unit berhasil disimpan',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit)
    {
        return response()->json([
            'data' => $unit,
            'success' => true,
            'message' => 'Data unit berhasil diambil',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:units,code,' . $unit->id,
            'description' => 'required',
        ]);
        $unit->update($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Data unit berhasil diupdate',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        $unit->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data unit berhasil dihapus',
        ]);
    }
}

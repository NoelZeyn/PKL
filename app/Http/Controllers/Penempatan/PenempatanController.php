<?php

namespace App\Http\Controllers\Penempatan;

use App\Http\Controllers\Controller;
use App\Models\Penempatan;
use Illuminate\Http\Request;

class PenempatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $penempatan = Penempatan::all();
            return response()->json([
                'status' => 'success',
                'data' => $penempatan,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve penempatan data',
            ], 500);
        }
    }

    public function getByBidang($id_bidang)
    {
        try {
            $penempatan = Penempatan::where('id_bidang_fk', $id_bidang)->get();

            return response()->json([
                'status' => 'success',
                'data' => $penempatan,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve penempatan by bidang',
            ], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nama_penempatan' => 'required|string|max:255',
            ]);
            $penempatan = Penempatan::create($validatedData);
            return response()->json([
                'status' => 'success',
                'data' => $penempatan,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create penempatan',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $penempatan = Penempatan::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $penempatan,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve penempatan data',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $penempatan = Penempatan::findOrFail($id);
            $validatedData = $request->validate([
                'nama_penempatan' => 'required|string|max:255',
            ]);
            $penempatan->update($validatedData);
            return response()->json([
                'status' => 'success',
                'data' => $penempatan,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update penempatan',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $penempatan = Penempatan::findOrFail($id);
            $penempatan->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Penempatan deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete penempatan',
            ], 500);
        }
    }
}

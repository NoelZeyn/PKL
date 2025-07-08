<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\DataDiri;
use Illuminate\Http\Request;

class DataDiriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $dataDiri = DataDiri::all();
            return response()->json([
                'status' => 'success',
                'data' => $dataDiri
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_admin_user_fk' => 'required|exists:admin_users,id',
                'nama_lengkap' => 'required|string|max:255',
                'jabatan' => 'required|string|max:255',
                'bpjs' => 'nullable|string|max:255',
            ]);

            $dataDiri = DataDiri::create($validated);
            return response()->json([
                'status' => 'success',
                'data' => $dataDiri
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $dataDiri = DataDiri::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $dataDiri
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data not found',
                'error' => $th->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $dataDiri = DataDiri::findOrFail($id);
            $validated = $request->validate([
                'nama_lengkap' => 'sometimes|required|string|max:255',
                'jabatan' => 'sometimes|required|string|max:255',
                'bpjs' => 'sometimes|nullable|string|max:255',
            ]);

            $dataDiri->update($validated);
            return response()->json([
                'status' => 'success',
                'data' => $dataDiri
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $dataDiri = DataDiri::findOrFail($id);
            $dataDiri->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Data deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete data',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}

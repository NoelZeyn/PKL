<?php

namespace App\Http\Controllers\Inventoris;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use Illuminate\Http\Request;

class AlatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $alat = Alat::all();
            return response()->json([
                'status' => 'success',
                'data' => $alat,
                'message' => 'Data alat retrieved successfully.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve data alat.'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validate = $request->validate([
                'id_kategori_fk' => 'required|exists:kategori_pengadaan,id_kategori',
                'nama_barang' => 'required|string|max:255',
                'stock_min' => 'nullable|integer|min:0',
                'stock_max' => 'nullable|integer|min:0',
                'stock' => 'nullable|integer|min:0',
                'satuan' => 'required|string|max:50',
                'harga_satuan' => 'required|numeric|min:0',
                'harga_estimasi' => 'required|numeric|min:0',
                'order' => 'nullable|integer',
                'keterangan' => 'nullable|string|max:500'
            ]);
            $alat = Alat::create($validate);
            return response()->json([
                'status' => 'success',
                'data' => $alat,
                'message' => 'Alat created successfully.'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create alat: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $alat = Alat::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $alat,
                'message' => 'Alat retrieved successfully.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve alat: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $alat = Alat::findOrFail($id);
            $validatedData = $request->validate([
                'id_kategori_fk' => 'required|exists:kategori_pengadaan,id_kategori',
                'nama_barang' => 'required|string|max:255',
                'stock_min' => 'nullable|integer|min:0',
                'stock_max' => 'nullable|integer|min:0',
                'stock' => 'nullable|integer|min:0',
                'satuan' => 'required|string|max:50',
                'harga_satuan' => 'nullable|numeric|min:0',
                'harga_estimasi' => 'nullable|numeric|min:0',
                'order' => 'nullable|integer',
                'keterangan' => 'nullable|string|max:500'
            ]);
            $alat->update($validatedData);
            return response()->json([
                'status' => 'success',
                'data' => $alat,
                'message' => 'Alat updated successfully.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update alat: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $alat = Alat::findOrFail($id);
            $alat->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Alat deleted successfully.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete alat: ' . $th->getMessage()
            ], 500);
        }   
    }
}

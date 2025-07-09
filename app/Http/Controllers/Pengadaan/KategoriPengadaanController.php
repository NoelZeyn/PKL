<?php

namespace App\Http\Controllers\Pengadaan;

use App\Http\Controllers\Controller;
use App\Models\KategoriPengadaan;
use Illuminate\Http\Request;

class KategoriPengadaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $kategori = KategoriPengadaan::all();
            return response()->json([
                'status' => 'success',
                'data' => $kategori,
                'message' => 'Data kategori pengadaan retrieved successfully.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve data kategori pengadaan.',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

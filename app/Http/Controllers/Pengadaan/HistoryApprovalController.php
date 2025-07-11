<?php

namespace App\Http\Controllers\Pengadaan;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use Illuminate\Http\Request;

class HistoryApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    try {
        $history = Approval::with(['request', 'admin.dataDiri'])
            ->orderByRaw("FIELD(status, 'approved') DESC")
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal mengambil data: ' . $th->getMessage()
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
        try {
            $history = Approval::with(['request', 'admin.dataDiri'])
                ->where('id_approval', $id)
                ->firstOrFail();
            return response()->json([
                'status' => 'success',
                'data' => $history
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan: ' . $th->getMessage()
            ], 404);
        }
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
        try {
            $history = Approval::findOrFail($id);
            $history->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data: ' . $th->getMessage()
            ], 500);
        }
    }
}

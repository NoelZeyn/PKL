<?php

namespace App\Http\Controllers\Pengadaan;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\RequestPengadaan;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $pengajuan = RequestPengadaan::with(['alat', 'user'])
                ->where('status', '!=', 'draft')
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json([
                'status' => 'success',
                'message' => 'Data pengajuan berhasil diambil',
                'data' => $pengajuan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengajuan gagal diambil',
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
            $validate = $request->validate([
                'id_inventoris_fk' => 'required|exists:alat,id_alat',
                'NID' => 'required|exists:admin,NID',
                'jumlah' => 'required|integer|min:1',
                'tanggal_permintaan' => 'required|date',
                'status' => 'required|in:draft,waiting_approval_1,approved_1,waiting_approval_2,approved_2,rejected',
                'total' => 'required|integer|min:0',
            ]);

            $admin = Admin::where('NID', $validate['NID'])->firstOrFail();

            $pengajuan = RequestPengadaan::create([
                'id_inventoris_fk' => $validate['id_inventoris_fk'],
                'id_users_fk' => $admin->id,
                'jumlah' => $validate['jumlah'],
                'tanggal_permintaan' => $validate['tanggal_permintaan'],
                'status' => $validate['status'],
                'total' => $validate['total'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data pengajuan berhasil disimpan',
                'data' => $pengajuan
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengajuan gagal disimpan',
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
            $pengajuan = RequestPengadaan::with(['alat', 'user'])
                ->where('id_request', $id)
                ->firstOrFail();
            return response()->json([
                'status' => 'success',
                'message' => 'Data pengajuan berhasil diambil',
                'data' => $pengajuan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengajuan tidak ditemukan',
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
            $validate = $request->validate([
                'id_inventoris_fk' => 'required|exists:alat,id_alat',
                'NID' => 'required|exists:admin,NID',
                'jumlah' => 'required|integer|min:1',
                'tanggal_permintaan' => 'required|date',
                'status' => 'required|in:draft,waiting_approval_1,approved_1,waiting_approval_2,approved_2,rejected',
                'total' => 'required|integer|min:0',
            ]);

            $admin = Admin::where('NID', $validate['NID'])->firstOrFail();
            $pengajuan = RequestPengadaan::findOrFail($id);

            $pengajuan->update([
                'id_inventoris_fk' => $validate['id_inventoris_fk'],
                'id_users_fk' => $admin->id,
                'jumlah' => $validate['jumlah'],
                'tanggal_permintaan' => $validate['tanggal_permintaan'],
                'status' => $validate['status'],
                'total' => $validate['total'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data pengajuan berhasil diperbarui',
                'data' => $pengajuan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengajuan gagal diperbarui',
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
            $pengajuan = RequestPengadaan::findOrFail($id);
            $pengajuan->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Data pengajuan berhasil dihapus'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pengajuan gagal dihapus',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\RequestPengadaan;
use Illuminate\Support\Facades\DB;
use PDO;

class ApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     */    public function approval1()
    {
        try {
            $requests = RequestPengadaan::with(['alat', 'user'])
                ->where('status', 'waiting_approval_1')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $requests,
                'message' => 'Data request menunggu Approval 2 berhasil diambil.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data: ' . $th->getMessage()
            ], 500);
        }
    }

    public function editApproval1(Request $request, string $id)
    {
        try {
            $validate = $request->validate([
                'id_inventoris_fk' => 'required|exists:alat,id_alat',
                'NID' => 'required|exists:admin,NID',
                'jumlah' => 'required|integer|min:1',
                'tanggal_permintaan' => 'required|date',
                'status' => 'required|in:waiting_approval_2,rejected',
                'total' => 'required|integer|min:0',
            ]);

            $admin = Admin::with('dataDiri')->where('NID', $validate['NID'])->firstOrFail();

            // Ambil nama lengkap admin atau fallback NID
            $namaLengkap = $admin->dataDiri && $admin->dataDiri->nama_lengkap
                ? $admin->dataDiri->nama_lengkap
                : $admin->NID;

            $pengajuan = RequestPengadaan::findOrFail($id);

            $pengajuan->update([
                'id_inventoris_fk' => $validate['id_inventoris_fk'],
                'id_users_fk' => $admin->id,
                'jumlah' => $validate['jumlah'],
                'tanggal_permintaan' => $validate['tanggal_permintaan'],
                'status' => $validate['status'],
                'total' => $validate['total'],
                'status_by' => $namaLengkap,
            ]);

            $levelApproval = $validate['status'] === 'waiting_approval_2' ? 'Approved Level 1' : 'Rejected Level 1';

            DB::table('approval')->insert([
                'id_request_fk' => $pengajuan->id_request,
                'id_admin_fk' => $admin->id,
                'level_approval' => $levelApproval,
                'status' => $validate['status'] === 'waiting_approval_2' ? 'approved' : 'rejected',
                'tanggal' => now()->toDateString(),
                'catatan' => $validate['catatan'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data pengajuan & riwayat approval berhasil diperbarui',
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

    public function approval2()
    {
        try {
            $requests = RequestPengadaan::with(['alat', 'user.dataDiri', 'user.penempatan'])
                ->where('status', 'waiting_approval_2')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $requests,
                'message' => 'Data request menunggu Approval 2 berhasil diambil.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data: ' . $th->getMessage()
            ], 500);
        }
    }

    public function editApproval2(Request $request, string $id)
    {
        try {
            $validate = $request->validate([
                'id_inventoris_fk' => 'required|exists:alat,id_alat',
                'NID' => 'required|exists:admin,NID',
                'jumlah' => 'required|integer|min:1',
                'tanggal_permintaan' => 'required|date',
                'status' => 'required|in:waiting_approval_3,rejected',
                'catatan' => 'nullable',
                'total' => 'required|integer|min:0',
            ]);

            $admin = Admin::with('dataDiri')->where('NID', $validate['NID'])->firstOrFail();

            // Ambil nama lengkap admin atau fallback NID
            $namaLengkap = $admin->dataDiri && $admin->dataDiri->nama_lengkap
                ? $admin->dataDiri->nama_lengkap
                : $admin->NID;

            $pengajuan = RequestPengadaan::findOrFail($id);

            $pengajuan->update([
                'id_inventoris_fk' => $validate['id_inventoris_fk'],
                'id_users_fk' => $admin->id,
                'jumlah' => $validate['jumlah'],
                'tanggal_permintaan' => $validate['tanggal_permintaan'],
                'status' => $validate['status'],
                'catatan' => $validate['catatan'],
                'total' => $validate['total'],
                'status_by' => $namaLengkap,
            ]);

            $levelApproval = $validate['status'] === 'waiting_approval_3' ? 'Approved Level 2' : 'Rejected Level 2';

            DB::table('approval')->insert([
                'id_request_fk' => $pengajuan->id_request,
                'id_admin_fk' => $admin->id,
                'level_approval' => $levelApproval,
                'status' => $validate['status'] === 'waiting_approval_3' ? 'approved' : 'rejected',
                'tanggal' => now()->toDateString(),
                'catatan' => $validate['catatan'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data pengajuan & riwayat approval berhasil diperbarui',
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


    public function approval3()
    {
        try {
            $requests = RequestPengadaan::with(['alat', 'user'])
                ->where('status', 'waiting_approval_3')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $requests,
                'message' => 'Data request menunggu Approval 3 berhasil diambil.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data: ' . $th->getMessage()
            ], 500);
        }
    }

    public function editApproval3(Request $request, string $id)
    {
        try {
            $validate = $request->validate([
                'id_inventoris_fk' => 'required|exists:alat,id_alat',
                'NID' => 'required|exists:admin,NID',
                'jumlah' => 'required|integer|min:1',
                'tanggal_permintaan' => 'required|date',
                'status' => 'required|in:approved,rejected',
                'total' => 'required|integer|min:0',
            ]);

            $admin = Admin::with('dataDiri')->where('NID', $validate['NID'])->firstOrFail();

            // Ambil nama lengkap admin atau fallback NID
            $namaLengkap = $admin->dataDiri->nama_lengkap
                ? $admin->dataDiri->nama_lengkap
                : $admin->NID;

            $pengajuan = RequestPengadaan::findOrFail($id);

            $pengajuan->update([
                'id_inventoris_fk' => $validate['id_inventoris_fk'],
                'id_users_fk' => $admin->id,
                'jumlah' => $validate['jumlah'],
                'tanggal_permintaan' => $validate['tanggal_permintaan'],
                'status' => $validate['status'],
                'total' => $validate['total'],
                'status_by' => $namaLengkap,
            ]);

            $levelApproval = $validate['status'] === 'approved' ? 'Approved Level 3' : 'Rejected Level 3';

            DB::table('approval')->insert([
                'id_request_fk' => $pengajuan->id_request,
                'id_admin_fk' => $admin->id,
                'level_approval' => $levelApproval,
                'status' => $validate['status'] === 'approved' ? 'approved' : 'rejected',
                'tanggal' => now()->toDateString(),
                'catatan' => $validate['catatan'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data pengajuan & riwayat approval berhasil diperbarui',
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

    public function index()
    {
        try {
            $request = RequestPengadaan::with(['alat', 'user.dataDiri', 'approvals'])->get();
            return response()->json([
                'status' => 'success',
                'data' => $request,
                'message' => 'Data approval berhasil diambil.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data: ' . $th->getMessage()
            ], 500);
        }
    }
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
    public function update(Request $request, string $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

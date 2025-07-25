<?php

namespace App\Http\Controllers\Pengadaan;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Alat;
use App\Models\RequestPengadaan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $pengajuan = RequestPengadaan::with(['alat', 'user.dataDiri', 'approvals','user.penempatan.bidang'])
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
    public function pengajuanPerByTahun(Request $request)
{
    try {
        $tahun = $request->tahun; // Ambil dari query param (opsional)

        $query = RequestPengadaan::with(['alat', 'user.dataDiri', 'approvals'])
            ->where('status', '!=', 'draft');

        if ($tahun) {
            $query->whereYear('tanggal_permintaan', $tahun);
        }

        $pengajuan = $query->orderBy('tanggal_permintaan', 'desc')->get();

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

    public function pengajuanPerSemesterByTahun(Request $request)
    {
        try {
            $tahun = $request->tahun ?? now()->year;

            // Ambil semua request non-draft berdasarkan tahun
            $requests = RequestPengadaan::where('status', '!=', 'draft')
                ->whereYear('tanggal_permintaan', $tahun)
                ->get();

            // Inisialisasi struktur semester
            $semesterStatusData = [
                'Semester 1' => [],
                'Semester 2' => [],
            ];

            foreach ($requests as $item) {
                $month = Carbon::parse($item->tanggal_permintaan)->month;
                $semester = $month <= 6 ? 'Semester 1' : 'Semester 2';

                $status = $item->status ?? 'unknown';

                if (!isset($semesterStatusData[$semester][$status])) {
                    $semesterStatusData[$semester][$status] = 0;
                }

                $semesterStatusData[$semester][$status]++;
            }

            return response()->json([
                'status' => 'success',
                'tahun' => $tahun,
                'data' => $semesterStatusData
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

public function getByPenempatan(Request $request)
{
    try {
        $user = Auth::user();
        if (!$user || !$user->id_penempatan_fk) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak memiliki penempatan atau belum login',
            ], 403);
        }

        $idPenempatan = $user->id_penempatan_fk;
        $tahun = $request->tahun ?? now()->year;

        $pengajuan = RequestPengadaan::with(['alat'])
            ->where('status', '!=', 'draft')
            ->whereYear('tanggal_permintaan', $tahun)
            ->whereHas('user', function ($query) use ($idPenempatan) {
                $query->where('id_penempatan_fk', $idPenempatan);
            })
            ->get();

        // Inisialisasi struktur
        $result = [
            "Semester 1" => [],
            "Semester 2" => [],
        ];

        foreach ($pengajuan as $item) {
            $month = \Carbon\Carbon::parse($item->tanggal_permintaan)->month;
            $semester = $month <= 6 ? "Semester 1" : "Semester 2";
            $result[$semester][] = $item;
        }

        return response()->json([
            'status' => 'success',
            'tahun' => $tahun,
            'data' => $result
        ]);

    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal mengambil data',
            'error' => $th->getMessage()
        ], 500);
    }
}




    /**
     * Store a newly created resource in storage.
     */
    private function hitungTotal($idAlat, $jumlah)
    {
        $alat = Alat::find($idAlat);
        if (!$alat) return 0;

        return $alat->harga_satuan * $jumlah;
    }

    public function storeMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.NID' => 'required|string|exists:admin,NID',
            'items.*.id_inventoris_fk' => 'required|exists:alat,id_alat',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        foreach ($validated['items'] as $item) {
            $admin = Admin::where('NID', $item['NID'])->first();
            if (!$admin) {
                continue;
            }

            RequestPengadaan::create([
                'NID' => $item['NID'],
                'id_inventoris_fk' => $item['id_inventoris_fk'],
                'id_users_fk' => $admin->id,
                'jumlah' => $item['jumlah'],
                'total' => $this->hitungTotal($item['id_inventoris_fk'], $item['jumlah']),
                'status' => 'waiting_approval_1',
                'tanggal_permintaan' => now()->format('Y-m-d'),
                'keterangan' => $item['keterangan'] ?? '-',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan berhasil disimpan.'
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validate = $request->validate([
                'id_inventoris_fk' => 'required|exists:alat,id_alat',
                'NID' => 'required|exists:admin,NID',
                'jumlah' => 'required|integer|min:1',
                'tanggal_permintaan' => 'required|date',
                'status' => 'required|in:draft,waiting_approval_1,waiting_approval_2,waiting_approval_3,approved,rejected',
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
    // public function update(Request $request, string $id)
    // {
    //     try {
    //         $validate = $request->validate([
    //             'id_inventoris_fk' => 'required|exists:alat,id_alat',
    //             'NID' => 'required|exists:admin,NID',
    //             'jumlah' => 'required|integer|min:1',
    //             'tanggal_permintaan' => 'required|date',
    //             'status' => 'required|in:draft,waiting_approval_2,waiting_approval_3,approved,rejected',
    //             'total' => 'required|integer|min:0',
    //         ]);

    //         $admin = Admin::with('dataDiri')->where('NID', $validate['NID'])->firstOrFail();
    //         $namaLengkap = $admin->dataDiri ? $admin->dataDiri->nama_lengkap : 'Unknown User';
    //         $pengajuan = RequestPengadaan::findOrFail($id);

    //         $pengajuan->update([
    //             'id_inventoris_fk' => $validate['id_inventoris_fk'],
    //             'id_users_fk' => $admin->id,
    //             'jumlah' => $validate['jumlah'],
    //             'tanggal_permintaan' => $validate['tanggal_permintaan'],
    //             'status' => $validate['status'],
    //             'total' => $validate['total'],
    //             'status_by' => $validate['status'] . ' by ' . $namaLengkap,
    //         ]);

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Data pengajuan berhasil diperbarui',
    //             'data' => $pengajuan
    //         ], 200);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Data pengajuan gagal diperbarui',
    //             'error' => $th->getMessage()
    //         ], 500);
    //     }
    // }

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

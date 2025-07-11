<?php

namespace App\Http\Controllers\Pengadaan;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\DataDiri;
use App\Models\Penempatan;
use App\Models\RequestPengadaan;
use App\Models\Bidang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    public function manajerAll()
    {
        $requests = RequestPengadaan::with(['alat', 'user.penempatan'])
            ->whereIn('status', ['waiting_approval_2'])
            ->get();

        $result = $this->processRequests($requests);

        return response()->json([
            'status' => 'success',
            'data'   => $result
        ]);
    }


    public function manajer(): JsonResponse
    {
        $user = Auth::user();
        $userBidangId = optional(optional($user)->penempatan)->id_bidang_fk;

        if (!$userBidangId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bidang tidak ditemukan untuk user ini.'
            ], 403);
        }

        $requests = RequestPengadaan::with(['alat', 'user.penempatan'])
            ->whereIn('status', ['waiting_approval_2'])
            ->whereHas('user.penempatan', function ($query) use ($userBidangId) {
                $query->where('id_bidang_fk', $userBidangId);
            })
            ->get();

        $result = $this->processRequests($requests);

        return response()->json([
            'status' => 'success',
            'data'   => $result
        ]);
    }
public function approveManajer(Request $request)
{
    $request->validate([
        'id_bidang_fk' => 'required|integer',
        'id_alat'      => 'required|integer',
    ]);

    $user = Auth::user();

    $dataDiri = DataDiri::where('id_admin_user_fk', $user->id)->first();
    $namaLengkap = $dataDiri ? $dataDiri->nama_lengkap : $user->NID ?? 'system';

    $updated = RequestPengadaan::whereHas('user', function ($query) use ($request) {
        $query->where('id_bidang_fk', $request->id_bidang_fk);
    })
    ->where('id_inventoris_fk', $request->id_alat)
    ->where('status', 'waiting_approval_2')
    ->update([
        'status' => 'waiting_approval_3',
        'status_by' => $namaLengkap,
    ]);

    return response()->json([
        'status'  => 'success',
        'message' => $updated > 0 ? 'Pengajuan berhasil disetujui.' : 'Tidak ada pengajuan yang bisa disetujui.',
    ]);
}

    public function rejectManajer(Request $request)
{
    $request->validate([
        'id_bidang_fk' => 'required|integer',
        'id_alat'      => 'required|integer',
        'keterangan'   => 'required|string|max:255',
    ]);

    $user = Auth::user();

    $dataDiri = DataDiri::where('id_admin_user_fk', $user->id)->first();
    $namaLengkap = $dataDiri ? $dataDiri->nama_lengkap : $user->NID ?? 'system';

    $updated = RequestPengadaan::whereHas('user.penempatan', function ($query) use ($request) {
        $query->where('id_bidang_fk', $request->id_bidang_fk);
    })
    ->where('id_inventoris_fk', $request->id_alat)
    ->where('status', 'waiting_approval_2')
    ->update([
        'status'     => 'rejected',
        'status_by'  => $namaLengkap,
        'keterangan' => $request->keterangan,
    ]);

    return response()->json([
        'status'  => 'success',
        'message' => $updated > 0 ? 'Pengajuan berhasil ditolak.' : 'Tidak ada pengajuan yang bisa ditolak.',
    ]);
}

    public function anggaranAll()
    {
        $requests = RequestPengadaan::with(['alat', 'user.penempatan'])
            ->whereIn('status', ['waiting_approval_3'])
            ->get();

        $result = $this->processRequests($requests);

        return response()->json([
            'status' => 'success',
            'data'   => $result
        ]);
    }


    public function asmanAll()
    {
        $requests = RequestPengadaan::with(['alat', 'user'])
            ->whereIn('status', ['waiting_approval_1'])
            ->get();
        $groupedRequests = $requests->groupBy(function ($item) {
            return optional($item->user)->id_penempatan_fk ?? 'unknown';
        });
        $result = [];

        foreach ($groupedRequests as $penempatanId => $items) {
            $barangData = [];
            $groupedByBarang = $items->groupBy(function ($item) {
                return optional($item->alat)->id_alat ?? 'unknown';
            });

            foreach ($groupedByBarang as $idAlat => $groupedItems) {
                $alat = $groupedItems->first()->alat;
                $totalJumlah = $groupedItems->sum('jumlah');
                $hargaSatuan = optional($groupedItems->first()->alat)->harga_satuan ?? 0;
                $totalHarga = $totalJumlah * $hargaSatuan;

                $barangData[] = [
                    'id_alat' => $idAlat,
                    'nama_barang'   => $alat->nama_barang,
                    'total_jumlah'  => $totalJumlah,
                    'harga_satuan'  => $hargaSatuan,
                    'total_harga'   => $totalHarga,
                ];
            }

            $penempatanNama = 'Tidak Diketahui';
            if ($penempatanId !== 'unknown') {
                $penempatan = Penempatan::find($penempatanId);
                if ($penempatan) {
                    $penempatanNama = $penempatan->nama_penempatan;
                }
            }

            $result[] = [
                'id_penempatan_fk' => $penempatanId,
                'nama_penempatan'  => $penempatanNama,
                'barang'           => $barangData,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => $result
        ]);
    }

    public function asman()
    {
        $user = Auth::user();
        $penempatanIdAsman = $user->id_penempatan_fk;

        $requests = RequestPengadaan::with(['alat', 'user'])
            ->whereIn('status', ['waiting_approval_1'])
            ->whereHas('user', function ($query) use ($penempatanIdAsman) {
                $query->where('id_penempatan_fk', $penempatanIdAsman);
            })
            ->get();

        $groupedRequests = $requests->groupBy(function ($item) {
            return optional($item->user)->id_penempatan_fk ?? 'unknown';
        });

        $result = [];

        foreach ($groupedRequests as $penempatanId => $items) {
            $barangData = [];

            $groupedByBarang = $items->groupBy(function ($item) {
                return optional($item->alat)->id_alat ?? 'unknown';
            });

            foreach ($groupedByBarang as $idAlat => $groupedItems) {
                $alat = $groupedItems->first()->alat;
                $totalJumlah = $groupedItems->sum('jumlah');
                $hargaSatuan = $alat->harga_satuan ?? 0;
                $totalHarga = $totalJumlah * $hargaSatuan;

                $barangData[] = [
                    'id_alat'      => $idAlat,
                    'nama_barang'  => $alat->nama_barang ?? 'Tidak Diketahui',
                    'total_jumlah' => $totalJumlah,
                    'harga_satuan' => $hargaSatuan,
                    'total_harga'  => $totalHarga,
                ];
            }

            $penempatanNama = 'Tidak Diketahui';
            if ($penempatanId !== 'unknown') {
                $penempatan = Penempatan::find($penempatanId);
                if ($penempatan) {
                    $penempatanNama = $penempatan->nama_penempatan;
                }
            }

            $result[] = [
                'id_penempatan_fk' => $penempatanId,
                'nama_penempatan'  => $penempatanNama,
                'barang'           => $barangData,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => $result
        ]);
    }

public function approveAsman(Request $request)
{
    $request->validate([
        'id_penempatan_fk' => 'required|integer',
        'id_alat' => 'required|integer',
    ]);

    $user = Auth::user();
    $dataDiri = DataDiri::where('id_admin_user_fk', $user->id)->first();
    $namaLengkap = $dataDiri ? $dataDiri->nama_lengkap : $user->NID ?? 'system';

    $requests = RequestPengadaan::whereHas('user', function ($query) use ($request) {
            $query->where('id_penempatan_fk', $request->id_penempatan_fk);
        })
        ->where('id_inventoris_fk', $request->id_alat)
        ->where('status', 'waiting_approval_1')
        ->get();

    foreach ($requests as $req) {
        $req->update([
            'status' => 'waiting_approval_2',
            'status_by' => $namaLengkap,
        ]);

        Approval::create([
            'id_request_fk' => $req->id_request,
            'id_admin_fk' => $user->id,
            'level_approval' => 'Asman',
            'status' => 'approved',
            'tanggal' => now()->toDateString(),
            'catatan' => null,
        ]);
    }

    return response()->json([
        'status' => 'success',
        'message' => $requests->count() > 0 ? 'Pengajuan berhasil disetujui.' : 'Tidak ada pengajuan yang bisa disetujui.',
    ]);
}


    /**
     * Reject semua request by penempatan → ✖
     */

public function rejectAsman(Request $request)
{
    $request->validate([
        'id_penempatan_fk' => 'required|integer',
        'id_alat' => 'required|integer',
        'keterangan' => 'required|string|max:255',
    ]);

    $user = Auth::user();
    $dataDiri = DataDiri::where('id_admin_user_fk', $user->id)->first();
    $namaLengkap = $dataDiri ? $dataDiri->nama_lengkap : $user->NID ?? 'system';

    $requests = RequestPengadaan::whereHas('user', function ($query) use ($request) {
            $query->where('id_penempatan_fk', $request->id_penempatan_fk);
        })
        ->where('id_inventoris_fk', $request->id_alat)
        ->where('status', 'waiting_approval_1')
        ->get();

    foreach ($requests as $req) {
        $req->update([
            'status' => 'rejected',
            'status_by' => $namaLengkap,
            'keterangan' => $request->keterangan,
        ]);

        Approval::create([
            'id_request_fk' => $req->id_request,
            'id_admin_fk' => $user->id,
            'level_approval' => 'Asman',
            'status' => 'rejected',
            'tanggal' => now()->toDateString(),
            'catatan' => $request->keterangan,
        ]);
    }

    return response()->json([
        'status' => 'success',
        'message' => $requests->count() > 0 ? 'Pengajuan berhasil ditolak.' : 'Tidak ada pengajuan yang bisa ditolak.',
    ]);
}


    /**
     * Proses pengelompokan dan perhitungan data pengajuan.
     */
    private function processRequests($requests)
    {
        $groupedRequests = $requests->groupBy(function ($item) {
            return optional(optional($item->user)->penempatan)->id_bidang_fk ?? 'unknown';
        });

        $result = [];

        foreach ($groupedRequests as $bidangId => $items) {
            $totalJumlahSeluruhBarang = 0;
            $totalHargaSeluruhBarang = 0;
            $barangData = [];

            $groupedByBarang = $items->groupBy(function ($item) {
                return optional($item->alat)->id_alat ?? 'Tidak Diketahui';
            });

            foreach ($groupedByBarang as $idAlat => $groupedItems) {
                $alat = $groupedItems->first()->alat;
                $namaBarang = $alat->nama_barang ?? 'Tidak Diketahui';
                $hargaSatuan = $alat->harga_satuan ?? 0;
                $totalJumlah = $groupedItems->sum('jumlah');
                $totalHarga = $totalJumlah * $hargaSatuan;

                $totalJumlahSeluruhBarang += $totalJumlah;
                $totalHargaSeluruhBarang += $totalHarga;

                $barangData[] = [
                    'id_alat'      => $idAlat,          // ✅ Hanya kirim ID, bukan object
                    'nama_barang'  => $namaBarang,
                    'harga_satuan' => $hargaSatuan,
                    'total_jumlah' => $totalJumlah,
                    'total_harga'  => $totalHarga,
                ];
            }

            $bidangNama = 'Tidak Diketahui';
            if ($bidangId !== 'unknown') {
                $bidang = Bidang::find($bidangId);
                if ($bidang) {
                    $bidangNama = $bidang->nama_bidang;
                }
            }

            $result[] = [
                'id_bidang_fk'        => $bidangId,
                'nama_bidang'         => $bidangNama,
                'total_jumlah_barang' => $totalJumlahSeluruhBarang,
                'total_harga_barang'  => $totalHargaSeluruhBarang,
                'barang'              => $barangData,
            ];
        }

        return $result;
    }
}

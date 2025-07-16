<?php

namespace App\Http\Controllers\Pengadaan;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Alat;
use App\Models\AlatPenempatan;
use App\Models\Approval;
use App\Models\DataDiri;
use App\Models\Penempatan;
use App\Models\RequestPengadaan;
use App\Models\Bidang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PengajuanIntiController extends Controller
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
        try {
            $request->validate([
                'id_bidang_fk' => 'required|integer',
                'id_alat'      => 'required|integer',
            ]);

            $user = Auth::user();
            $dataDiri = DataDiri::where('id_admin_user_fk', $user->id)->first();
            $namaLengkap = $dataDiri ? $dataDiri->nama_lengkap : $user->NID ?? 'system';

            // Ambil SEMUA pengajuan yang memenuhi syarat
            $pengajuanList = RequestPengadaan::whereHas('user.penempatan', function ($query) use ($request) {
                $query->where('id_bidang_fk', $request->id_bidang_fk);
            })
                ->where('id_inventoris_fk', $request->id_alat)
                ->where('status', 'waiting_approval_2')
                ->get();

            if ($pengajuanList->isEmpty()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Tidak ada pengajuan yang bisa disetujui.',
                    'data'    => null
                ]);
            }

            foreach ($pengajuanList as $pengajuan) {
                $pengajuan->status = 'waiting_approval_3';
                $pengajuan->status_by = $namaLengkap;
                $pengajuan->save();
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Semua pengajuan berhasil disetujui.',
                'data'    => $pengajuanList
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }



    public function rejectManajer(Request $request)
    {
        try {
            $request->validate([
                'id_bidang_fk' => 'required|integer',
                'id_alat'      => 'required|integer',
                'keterangan'   => 'required|string|max:255',
            ]);

            $user = Auth::user();
            $dataDiri = DataDiri::where('id_admin_user_fk', $user->id)->first();
            $namaLengkap = $dataDiri ? $dataDiri->nama_lengkap : $user->NID ?? 'system';

            $pengajuanList = RequestPengadaan::whereHas('user.penempatan', function ($query) use ($request) {
                $query->where('id_bidang_fk', $request->id_bidang_fk);
            })
                ->where('id_inventoris_fk', $request->id_alat)
                ->where('status', 'waiting_approval_2')
                ->get();

            if ($pengajuanList->isEmpty()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Tidak ada pengajuan yang bisa ditolak.',
                    'data'    => null
                ]);
            }

            foreach ($pengajuanList as $pengajuan) {
                $pengajuan->status = 'rejected';
                $pengajuan->status_by = $namaLengkap;
                $pengajuan->keterangan = $request->keterangan;
                $pengajuan->save();
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Semua pengajuan berhasil ditolak.',
                'data'    => $pengajuanList
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
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

    public function approveAnggaran(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'id_bidang_fk' => 'required|integer',
            ]);

            $user = Auth::user();
            $dataDiri = DataDiri::where('id_admin_user_fk', $user->id)->first();
            $namaLengkap = $dataDiri ? $dataDiri->nama_lengkap : ($user->NID ?? 'system');

            // Ambil semua pengajuan yang menunggu approval anggaran dari bidang terkait
            $requests = RequestPengadaan::whereHas('user.penempatan', function ($query) use ($request) {
                $query->where('id_bidang_fk', $request->id_bidang_fk);
            })
                ->where('status', 'waiting_approval_3')
                ->get();

            if ($requests->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Tidak ada pengajuan yang bisa disetujui.',
                ]);
            }

            $alatIds = collect();

            $requests->each(function ($req) use ($namaLengkap, $user, &$alatIds) {
                $req->update([
                    'status'     => 'approved',
                    'status_by'  => $namaLengkap,
                ]);
                $alatIds->push($req->id_inventoris_fk);

                Approval::create([
                    'id_request_fk'   => $req->id_request,
                    'id_admin_fk'     => $user->id,
                    'level_approval'  => 'Anggaran',
                    'status'          => 'approved',
                    'tanggal'         => now()->toDateString(),
                    'catatan'         => null,
                ]);
            });

            // Update order di tabel alat berdasarkan total pengajuan yang sudah di-approve
            Alat::whereIn('id_alat', $alatIds->unique())->get()->each(function ($alat) {
                $totalOrder = RequestPengadaan::where('id_inventoris_fk', $alat->id_alat)
                    ->where('status', 'approved')
                    ->sum('jumlah');
                $alat->update(['order' => $totalOrder]);
            });

            DB::commit();
            return response()->json([
                'status'  => 'success',
                'message' => 'Pengajuan berhasil disetujui.',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }


    public function rejectAnggaran(Request $request)
    {
        try {
            $request->validate([
                'id_bidang_fk' => 'required|integer',
                'keterangan'   => 'required|string|max:255',
            ]);

            $user = Auth::user();
            $dataDiri = DataDiri::where('id_admin_user_fk', $user->id)->first();
            $namaLengkap = $dataDiri ? $dataDiri->nama_lengkap : $user->NID ?? 'system';

            $requests = RequestPengadaan::whereHas('user.penempatan', function ($query) use ($request) {
                $query->where('id_bidang_fk', $request->id_bidang_fk);
            })
                ->where('status', 'waiting_approval_3')
                ->get();

            foreach ($requests as $req) {
                $req->update([
                    'status'      => 'rejected',
                    'status_by'   => $namaLengkap,
                    'keterangan'  => $request->keterangan,
                ]);

                Approval::create([
                    'id_request_fk'   => $req->id_request,
                    'id_admin_fk'     => $user->id,
                    'level_approval'  => 'Anggaran',
                    'status'          => 'rejected',
                    'tanggal'         => now()->toDateString(),
                    'catatan'         => $request->keterangan,
                ]);
            }

            return response()->json([
                'status'  => 'success',
                'message' => $requests->count() > 0 ? 'Pengajuan berhasil ditolak.' : 'Tidak ada pengajuan yang bisa ditolak.',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
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
        try {
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
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
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

    public function pengajuanAdminTable()
    {
        try {
            $requests = RequestPengadaan::with(['alat', 'user.penempatan.bidang'])
                ->whereNotIn('status', ['waiting_approval_1', 'waiting_approval_2', 'waiting_approval_3', 'rejected'])
                ->get();

            $grouped = $requests->groupBy(function ($item) {
                return optional(optional($item->user)->penempatan)->id_bidang_fk ?? 'unknown';
            });

            $result = [];

            foreach ($grouped as $bidangId => $items) {
                $bidangNama = 'Tidak Diketahui';
                if ($bidangId !== 'unknown') {
                    $bidang = Bidang::find($bidangId);
                    if ($bidang) {
                        $bidangNama = $bidang->nama_bidang;
                    }
                }

                // Gabungkan barang berdasarkan ID alat
                $barangMap = [];

                foreach ($items as $item) {
                    $barang = $item->alat;
                    $idAlat = $item->id_inventoris_fk;
                    $namaBarang = $barang->nama_barang ?? 'Tidak Diketahui';
                    $hargaSatuan = $barang->harga_satuan ?? 0;
                    $jumlah = $item->jumlah;

                    if (!isset($barangMap[$idAlat])) {
                        $barangMap[$idAlat] = [
                            'id_alat'      => $idAlat,
                            'nama_barang'  => $namaBarang,
                            'jumlah'       => 0,
                            'harga_satuan' => $hargaSatuan,
                            'total_harga'  => 0,
                            'status'       => $item->status ?? '-',
                            'keterangan'   => $item->keterangan ?? '-',
                        ];
                    }

                    $barangMap[$idAlat]['jumlah'] += $jumlah;
                    $barangMap[$idAlat]['total_harga'] += $jumlah * $hargaSatuan;
                }

                $result[] = [
                    'id_bidang_fk' => $bidangId,
                    'nama_bidang'  => $bidangNama,
                    'barang'       => array_values($barangMap), // Reset indeks
                ];
            }

            return response()->json([
                'status' => 'success',
                'data'   => $result
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }
    public function pengajuanAdminTableTahun(Request $request)
    {

        try {
            $tahun = $request->input('tahun', now()->year);

            $requests = RequestPengadaan::with(['alat', 'user.penempatan.bidang'])
                ->whereNotIn('status', ['waiting_approval_1', 'waiting_approval_2', 'waiting_approval_3', 'rejected'])
                ->whereYear('tanggal_permintaan', $tahun)
                ->get();

            $grouped = $requests->groupBy(function ($item) {
                return optional(optional($item->user)->penempatan)->id_bidang_fk ?? 'unknown';
            });

            $result = [];

            foreach ($grouped as $bidangId => $items) {
                $bidangNama = 'Tidak Diketahui';
                if ($bidangId !== 'unknown') {
                    $bidang = Bidang::find($bidangId);
                    if ($bidang) {
                        $bidangNama = $bidang->nama_bidang;
                    }
                }

                $barangMap = [];

                foreach ($items as $item) {
                    $barang = $item->alat;
                    $idAlat = $item->id_inventoris_fk;
                    $namaBarang = $barang->nama_barang ?? 'Tidak Diketahui';
                    $hargaSatuan = $barang->harga_satuan ?? 0;
                    $jumlah = $item->jumlah;

                    if (!isset($barangMap[$idAlat])) {
                        $barangMap[$idAlat] = [
                            'id_alat'      => $idAlat,
                            'nama_barang'  => $namaBarang,
                            'jumlah'       => 0,
                            'harga_satuan' => $hargaSatuan,
                            'total_harga'  => 0,
                            'status'       => $item->status ?? '-',
                            'keterangan'   => $item->keterangan ?? '-',
                        ];
                    }

                    $barangMap[$idAlat]['jumlah'] += $jumlah;
                    $barangMap[$idAlat]['total_harga'] += $jumlah * $hargaSatuan;
                }

                $result[] = [
                    'id_bidang_fk' => $bidangId,
                    'nama_bidang'  => $bidangNama,
                    'barang'       => array_values($barangMap),
                ];
            }

            return response()->json([
                'status' => 'success',
                'data'   => $result
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function pengajuanAdminTableBySemester(Request $request)
    {
        try {
            $tahun = $request->tahun ?? now()->year;

            $requests = RequestPengadaan::with(['alat', 'user.penempatan.bidang'])
                ->whereYear('tanggal_permintaan', $tahun)

                ->whereNotIn('status', ['waiting_approval_1', 'waiting_approval_2', 'waiting_approval_3', 'rejected'])
                ->get();

            $semesterGroups = [
                'Semester 1' => [],
                'Semester 2' => [],
            ];

            foreach ($requests as $item) {
                $bulan = Carbon::parse($item->tanggal_permintaan)->month;

                $semesterKey = $bulan <= 6 ? 'Semester 1' : 'Semester 2';
                $semesterGroups[$semesterKey][] = $item;
            }

            $result = [];

            foreach ($semesterGroups as $semester => $items) {
                $grouped = collect($items)->groupBy(function ($item) {
                    return optional(optional($item->user)->penempatan)->id_bidang_fk ?? 'unknown';
                });

                $semesterResult = [];

                foreach ($grouped as $bidangId => $data) {
                    $bidangNama = 'Tidak Diketahui';
                    if ($bidangId !== 'unknown') {
                        $bidang = Bidang::find($bidangId);
                        if ($bidang) {
                            $bidangNama = $bidang->nama_bidang;
                        }
                    }

                    // Gabungkan barang berdasarkan alat
                    $barangMap = [];

                    foreach ($data as $item) {
                        $barang = $item->alat;
                        $idAlat = $item->id_inventoris_fk;
                        $namaBarang = $barang->nama_barang ?? 'Tidak Diketahui';
                        $hargaSatuan = $barang->harga_satuan ?? 0;
                        $jumlah = $item->jumlah;

                        if (!isset($barangMap[$idAlat])) {
                            $barangMap[$idAlat] = [
                                'id_alat'      => $idAlat,
                                'nama_barang'  => $namaBarang,
                                'jumlah'       => 0,
                                'harga_satuan' => $hargaSatuan,
                                'total_harga'  => 0,
                                'status'       => $item->status ?? '-',
                                'keterangan'   => $item->keterangan ?? '-',
                            ];
                        }

                        $barangMap[$idAlat]['jumlah'] += $jumlah;
                        $barangMap[$idAlat]['total_harga'] += $jumlah * $hargaSatuan;
                    }

                    $semesterResult[] = [
                        'id_bidang_fk' => $bidangId,
                        'nama_bidang'  => $bidangNama,
                        'barang'       => array_values($barangMap),
                    ];
                }

                $result[$semester] = $semesterResult;
            }

            return response()->json([
                'status' => 'success',
                'data'   => $result
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'id_alat' => 'required|integer',
            'status'  => 'required|in:approved,purchasing,on_the_way,done',
        ]);

        $requests = RequestPengadaan::where('id_inventoris_fk', $request->id_alat)
            ->whereNotIn('status', ['waiting_approval_1', 'waiting_approval_2', 'waiting_approval_3', 'rejected'])
            ->get();

        if ($requests->isEmpty()) {
            return response()->json([
                'message' => 'Data tidak ditemukan atau bukan status yang dapat diubah'
            ], 404);
        }

        $allowedTransitions = [
            'approved'    => ['purchasing'],
            'purchasing'  => ['on_the_way'],
            'on_the_way'  => ['done'],
        ];

        foreach ($requests as $req) {
            $currentStatus = $req->status;
            $newStatus = $request->status;

            if (!isset($allowedTransitions[$currentStatus]) || !in_array($newStatus, $allowedTransitions[$currentStatus])) {
                continue;
            }

            $req->status = $newStatus;
            $req->save();

            if ($newStatus === 'done') {
                // Tambah ke pusat stock
                $this->updateStokAlat($req->id_inventoris_fk, $req->jumlah);

                // Tambah ke stok penempatan jika admin punya penempatan
                $admin = Admin::with('penempatan')->find($req->id_users_fk);
                $id_penempatan = optional($admin->penempatan)->id;

                if ($id_penempatan) {
                    $this->tambahStokPenempatan($req->id_inventoris_fk, $id_penempatan, $req->jumlah);
                }
            }

            $catatan = $newStatus === 'done'
                ? "Barang telah diterima dan stok diperbarui oleh " . ($user->dataDiri->nama_lengkap ?? 'Staff')
                : "Status diubah dari \"$currentStatus\" ke \"$newStatus\" oleh " . ($user->dataDiri->nama_lengkap ?? 'Staff');

            Approval::create([
                'id_request_fk'  => $req->id_request,
                'id_admin_fk'    => $user->id,
                'level_approval' => 'Staff System',
                'status'         => $newStatus,
                'tanggal'        => now()->toDateString(),
                'catatan'        => $catatan,
            ]);
        }

        return response()->json(['message' => 'Status berhasil diupdate untuk semua pengajuan terkait.']);
    }

    private function updateStokAlat($idAlat, $jumlah)
    {
        $alat = Alat::find($idAlat);

        if (!$alat) return;

        $alat->order = $alat->order ?? 0;
        $alat->stock = $alat->stock ?? 0;

        $alat->order = max(0, $alat->order - $jumlah); // Hindari minus
        $alat->stock += $jumlah;

        $alat->save();
    }

    private function tambahStokPenempatan($idAlat, $idPenempatan, $jumlah)
    {
        $alatPenempatan = AlatPenempatan::where('id_alat_fk', $idAlat)
            ->where('id_penempatan_fk', $idPenempatan)
            ->first();

        if ($alatPenempatan) {
            $alatPenempatan->stock += $jumlah;
            $alatPenempatan->save();
        } else {
            AlatPenempatan::create([
                'id_alat_fk' => $idAlat,
                'id_penempatan_fk' => $idPenempatan,
                'stock' => $jumlah,
                'stock_min' => 0,
                'stock_max' => 0,
            ]);
        }
    }
}

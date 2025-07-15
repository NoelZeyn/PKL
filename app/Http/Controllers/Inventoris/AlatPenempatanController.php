<?php

namespace App\Http\Controllers\Inventoris;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\Penempatan;
use App\Models\AlatPenempatan;
use App\Models\HistoryAtk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AlatPenempatanController extends Controller
{
    // Tampilkan semua data alat per penempatan
    public function index()
    {
        try {
            $data = AlatPenempatan::with(['alat', 'penempatan'])->get();

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // Tampilkan semua alat berdasarkan penempatan tertentu
    public function getAlatByPenempatan($id_penempatan)
    {
        try {
            $penempatan = Penempatan::findOrFail($id_penempatan);
            $alatList = $penempatan->alat()->with(['kategori'])->get();

            $data = $alatList->map(function ($alat) use ($id_penempatan) {
                $terbagiKe = $alat->penempatan->map(function ($penempatan) {
                    return [
                        'id_penempatan' => $penempatan->id,
                        'nama_penempatan' => $penempatan->nama_penempatan,
                        'stock' => $penempatan->pivot->stock,
                        'stock_min' => $penempatan->pivot->stock_min,
                        'stock_max' => $penempatan->pivot->stock_max,
                    ];
                });

                $pivot = $alat->penempatan->firstWhere('id', $id_penempatan)?->pivot;

                return [
                    'id_alat' => $alat->id_alat,
                    'nama_barang' => $alat->nama_barang,
                    'pusat_stock' => $alat->stock,
                    'stock' => $pivot->stock ?? 0,
                    'stock_min' => $pivot->stock_min ?? 0,
                    'stock_max' => $pivot->stock_max ?? 0,
                    'satuan' => $alat->satuan,
                    'harga_satuan' => $alat->harga_satuan,
                    'harga_estimasi' => $alat->harga_estimasi,
                    'keterangan' => $alat->keterangan,
                    'kategori' => $alat->kategori->nama_kategori ?? '-',
                    'terbagi_ke' => $terbagiKe,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    [
                        'id_penempatan' => $penempatan->id,
                        'nama_penempatan' => $penempatan->nama_penempatan,
                        'barang' => $data
                    ]
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function updateAlatPenempatan(Request $request, $id_alat, $id_penempatan)
    {
        $validator = Validator::make($request->all(), [
            'stock' => 'nullable|integer|min:0',
            'stock_min' => 'nullable|integer|min:0',
            'stock_max' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $alatPenempatan = AlatPenempatan::with('penempatan') // ← ini penting
                ->where('id_alat_fk', $id_alat)
                ->where('id_penempatan_fk', $id_penempatan)
                ->first();
            $namaPenempatan = $alatPenempatan->penempatan->nama_penempatan ?? '-';


            if (!$alatPenempatan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }

            $oldData = $alatPenempatan->toArray();

            // Update data
            $alatPenempatan->update([
                'stock' => $request->input('stock'),
                'stock_min' => $request->input('stock_min'),
                'stock_max' => $request->input('stock_max'),
            ]);

            // Bandingkan perubahan dan simpan ke history jika ada
            $changes = [];
            foreach (['stock', 'stock_min', 'stock_max'] as $key) {
                $old = $oldData[$key];
                $new = $alatPenempatan->$key;
                if ($old != $new) {
                    $changes[] = ucfirst($key) . " dari " . ($old ?? '-') . " → " . ($new ?? '-');
                }
            }

            if (count($changes) > 0) {
                HistoryAtk::create([
                    'id_admin_fk' => Auth::id(),
                    'id_alat_fk' => $id_alat,
                    'jenis_aksi' => 'edit',
                    'deskripsi' => 'Update stock ATK di penempatan: ' . $namaPenempatan . '. ' . implode(', ', $changes),
                    'jumlah' => $alatPenempatan->stock,
                    'tanggal' => now()->toDateString(),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Stock alat penempatan berhasil diperbarui.',
                'data' => $alatPenempatan
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getDistribusiAlat()
    {
        try {
            // Ambil semua alat, termasuk relasi penempatan di pivot
            $alatData = Alat::with(['penempatan'])->get();

            $result = $alatData->map(function ($alat) {
                return [
                    'id_alat'       => $alat->id_alat,
                    'nama_barang'   => $alat->nama_barang,
                    'pusat_stock'   => $alat->stock,
                    'satuan'        => $alat->satuan,
                    'keterangan'    => $alat->keterangan,
                    'harga_satuan'  => $alat->harga_satuan,
                    'harga_estimasi' => $alat->harga_estimasi,
                    'terbagi_ke'    => $alat->penempatan->map(function ($p) {
                        return [
                            'id_penempatan'   => $p->id,
                            'nama_penempatan' => $p->nama_penempatan,
                            'stock'           => $p->pivot->stock,
                            'stock_min'       => $p->pivot->stock_min,
                            'stock_max'       => $p->pivot->stock_max,
                        ];
                    })
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $result
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // Tambahkan atau update alat ke penempatan (jika sudah ada, akan diupdate)
    public function store(Request $request)
    {
        $request->validate([
            'id_alat_fk'        => 'required|exists:alat,id_alat',
            'id_penempatan_fk'  => 'required|exists:penempatan,id',
            'stock'             => 'required|integer|min:0',
            'stock_min'         => 'required|integer|min:0',
            'stock_max'         => 'required|integer|min:stock_min',
        ]);

        try {
            $pivot = AlatPenempatan::updateOrCreate(
                [
                    'id_alat_fk' => $request->id_alat_fk,
                    'id_penempatan_fk' => $request->id_penempatan_fk,
                ],
                [
                    'stock'     => $request->stock,
                    'stock_min' => $request->stock_min,
                    'stock_max' => $request->stock_max,
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Data alat untuk penempatan berhasil disimpan.',
                'data' => $pivot
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // Update hanya nilai stoknya
    public function update(Request $request)
    {
        $request->validate([
            'id_alat_fk'        => 'required|exists:alat,id_alat',
            'id_penempatan_fk'  => 'required|exists:penempatan,id',
            'stock'             => 'required|integer|min:0',
        ]);

        try {
            $pivot = AlatPenempatan::where('id_alat_fk', $request->id_alat_fk)
                ->where('id_penempatan_fk', $request->id_penempatan_fk)
                ->first();

            if (!$pivot) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }

            $pivot->stock = $request->stock;
            $pivot->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Stok berhasil diperbarui.',
                'data' => $pivot
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}

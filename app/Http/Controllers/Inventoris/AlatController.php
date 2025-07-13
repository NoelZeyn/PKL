<?php

namespace App\Http\Controllers\Inventoris;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\HistoryAtk;
use App\Models\RequestPengadaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $alat = Alat::with('kategori')->get();
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
            HistoryAtk::create([
                'id_admin_fk' => Auth::id(),
                'id_alat_fk' => $alat->id_alat,
                'jenis_aksi' => 'tambah',
                'deskripsi' => 'Menambahkan ATK: ' . $alat->nama_barang,
                'jumlah' => $alat->stock,
                'tanggal' => now()->toDateString(),
            ]);
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

        // Simpan data lama sebelum update
        $oldData = $alat->toArray();

        // Validasi data baru
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

        // Lakukan update
        $alat->update($validatedData);

        // Cek perbedaan & buat deskripsi perubahan
        $changes = [];
        foreach ($validatedData as $key => $newValue) {
            $oldValue = $oldData[$key] ?? null;

            if ($oldValue != $newValue) {
                $changes[] = ucfirst($key) . " dari " . ($oldValue ?? '-') . " → " . ($newValue ?? '-');
            }
        }

        // Jika ada perubahan, simpan ke history
        if (count($changes) > 0) {
            HistoryAtk::create([
                'id_admin_fk' => Auth::id(),
                'id_alat_fk' => $alat->id_alat,
                'jenis_aksi' => 'edit',
                'deskripsi' => 'Update ATK: ' . $alat->nama_barang . '. ' . implode(', ', $changes),
                'jumlah' => $alat->stock,
                'tanggal' => now()->toDateString(),
            ]);
        }

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
        $namaBarang = $alat->nama_barang;
        $idAlat = $alat->id_alat;

        // Cek jika sudah dinonaktifkan sebelumnya
        if (
            $alat->stock == 0 &&
            $alat->stock_min == 0 &&
            $alat->stock_max == 0 &&
            $alat->keterangan === 'ATK Tidak Digunakan'
        ) {
            return response()->json([
                'status' => 'info',
                'message' => 'Alat tidak bisa dihapus karena sudah dinonaktifkan dan masih memiliki riwayat penggunaan.'
            ], 200);
        }

        // Cek apakah terhubung ke request atau history
        $terhubungKeRequest = RequestPengadaan::where('id_inventoris_fk', $idAlat)->exists();
        $terhubungKeHistory = HistoryAtk::where('id_alat_fk', $idAlat)->exists();

        if ($terhubungKeRequest || $terhubungKeHistory) {
            // Tidak bisa dihapus → Nonaktifkan
            $alat->update([
                'stock_min' => 0,
                'stock_max' => 0,
                'stock' => 0,
                'keterangan' => 'ATK Tidak Digunakan',
            ]);

            // Buat log nonaktif
            HistoryAtk::create([
                'id_admin_fk' => Auth::id(),
                'id_alat_fk' => $idAlat,
                'jenis_aksi' => 'nonaktif',
                'deskripsi' => 'Menonaktifkan ATK (stock diset 0): ' . $namaBarang,
                'jumlah' => null,
                'tanggal' => now()->toDateString(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Alat tidak bisa dihapus karena terhubung dengan pengajuan atau histori. Stock telah diset ke 0.'
            ], 200);
        } else {
            // Hapus alat terlebih dahulu
            $alat->delete();

            // Baru buat log penghapusan setelah berhasil
            HistoryAtk::create([
                'id_admin_fk' => Auth::id(),
                'id_alat_fk' => $idAlat,
                'jenis_aksi' => 'hapus',
                'deskripsi' => 'Menghapus ATK: ' . $namaBarang,
                'jumlah' => null,
                'tanggal' => now()->toDateString(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Alat berhasil dihapus.'
            ], 200);
        }
    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal menghapus/nonaktifkan alat: ' . $th->getMessage()
        ], 500);
    }
}


}

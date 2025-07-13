<?php

namespace App\Http\Controllers\Inventoris;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\RequestATKBaru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestATKBaruController extends Controller
{
    // ✅ List Semua Pengajuan
    public function index()
    {
        $pengajuan = RequestATKBaru::with(['kategori', 'user.dataDiri'])->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $pengajuan
        ]);
    }

    // ✅ Buat Banyak Pengajuan (oleh User)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id_kategori_fk' => 'required|exists:kategori_pengadaan,id_kategori',
            'items.*.nama_barang' => 'required|string|max:255',
            'items.*.satuan' => 'required|string|max:100',
            'items.*.harga_estimasi' => 'nullable|integer|min:0',
            'items.*.keterangan' => 'nullable|string',
        ]);

        $idUser = Auth::id(); // ambil dari token login

        $createdItems = [];

        foreach ($validated['items'] as $item) {
            $item['status'] = 'pending';
            $item['id_user_fk'] = $idUser;

            $created = RequestATKBaru::create($item);
            $createdItems[] = $created;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Semua pengajuan berhasil disimpan.',
            'data' => $createdItems
        ]);
    }

    // ✅ Tampilkan Detail Pengajuan
    public function show($id)
    {
        $pengajuan = RequestATKBaru::with(['kategori', 'user'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $pengajuan
        ]);
    }

    // ✅ Approve oleh Admin
    public function approve($id)
    {
        try {
            $user = Auth::user();
            $namaLengkap = optional($user->dataDiri)->nama_lengkap ?? $user->NID;

            $pengajuan = RequestATKBaru::findOrFail($id);
            $existingAlat = Alat::where('nama_barang', $pengajuan->nama_barang)->first();
            if (!$existingAlat) {
                // Buat alat baru jika belum ada
                Alat::create([
                    'id_kategori_fk' => $pengajuan->id_kategori_fk,
                    'nama_barang'    => $pengajuan->nama_barang,
                    'satuan'         => $pengajuan->satuan,
                    'harga_estimasi' => $pengajuan->harga_estimasi,
                    'stock_min'      => 1,
                    'stock_max'      => 1,
                    'stock'          => 0,
                    'order'          => 0,
                    'keterangan'     => $pengajuan->keterangan,
                ]);
            }
            $pengajuan->update([
                'status' => 'approved',
                'status_by' => $namaLengkap
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan telah disetujui.',
                'data' => $pengajuan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyetujui pengajuan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // ✅ Reject oleh Admin
    public function reject(Request $request, $id)
    {
        try {
            $user = Auth::user();

            $pengajuan = RequestATKBaru::findOrFail($id);
            $pengajuan->status = 'rejected';

            $pengajuan->catatan = $request->keterangan;

            $pengajuan->status_by = optional($user->dataDiri)->nama_lengkap ?? $user->NID;

            $pengajuan->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan telah ditolak.',
                'data' => $pengajuan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menolak pengajuan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ Hapus Pengajuan
    public function destroy($id)
    {
        $pengajuan = RequestATKBaru::findOrFail($id);
        $pengajuan->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Pengajuan berhasil dihapus.'
        ]);
    }
}

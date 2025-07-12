<?php

namespace App\Http\Controllers\Inventoris;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Alat;
use App\Models\HistoryPemakaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryPemakaianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $history = HistoryPemakaian::with(['alat', 'user.dataDiri'])
                ->orderBy('tanggal_pemakaian', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $history
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data riwayat pemakaian: ' . $e->getMessage()
            ], 500);
        }
    }
    public function storeMultiple(Request $request)
    {
        $request->validate([
            'pemakaian' => 'required|array|min:1',
            'pemakaian.*.NID' => 'required|exists:admin,NID',
            'pemakaian.*.id_alat' => 'required|exists:alat,id_alat',
            'pemakaian.*.jumlah' => 'required|integer|min:1',
            'pemakaian.*.keterangan' => 'nullable|string',
        ]);

        $results = [];

        foreach ($request->pemakaian as $data) {
            $admin = Admin::where('NID', $data['NID'])->first();
            if (!$admin) {
                $results[] = [
                    'status' => 'error',
                    'message' => "NID {$data['NID']} tidak ditemukan."
                ];
                continue;
            }

            $alat = Alat::find($data['id_alat']);
            if (!$alat) {
                $results[] = [
                    'status' => 'error',
                    'message' => "Alat dengan ID {$data['id_alat']} tidak ditemukan."
                ];
                continue;
            }

            if ($alat->stock < $data['jumlah']) {
                $results[] = [
                    'status' => 'error',
                    'message' => "Stok alat {$alat->nama_barang} tidak cukup."
                ];
                continue;
            }

            // Kurangi stok
            $alat->stock -= $data['jumlah'];
            $alat->save();

            $history = HistoryPemakaian::create([
                'id_alat' => $alat->id_alat,
                'id_user' => $admin->id,
                'jumlah' => $data['jumlah'],
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            $results[] = [
                'status' => 'success',
                'message' => "Pemakaian alat {$alat->nama_barang} berhasil dicatat.",
                'data' => $history
            ];
        }

        return response()->json([
            'status' => 'completed',
            'results' => $results
        ]);
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

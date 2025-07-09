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
            $history = HistoryPemakaian::with(['alat', 'user'])
                ->orderBy('tanggal_pemakaian', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $history
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data history pemakaian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'NID' => 'required|exists:admin,NID',
            'id_alat' => 'required|exists:alat,id_alat',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string'
        ]);
        $admin = Admin::where('NID', $request->NID)->first();
        if (!$admin) {
            return response()->json(['status' => 'error', 'message' => 'NID tidak ditemukan.'], 404);
        }
        $alat = Alat::where('id_alat', $request->id_alat)->firstOrFail();

        // Kurangi stok alat
        if ($alat->stock < $request->jumlah) {
            return response()->json(['status' => 'error', 'message' => 'Stok tidak cukup.'], 400);
        }

        $alat->stock -= $request->jumlah;
        $alat->save();

        $history = HistoryPemakaian::create([
            'id_alat' => $alat->id_alat,
            'id_user' => $admin->id,
            'jumlah' => $request->jumlah,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pemakaian alat berhasil dicatat.',
            'data' => $history
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

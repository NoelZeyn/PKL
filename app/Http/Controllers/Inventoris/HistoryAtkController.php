<?php

namespace App\Http\Controllers\Inventoris;

use App\Http\Controllers\Controller;
use App\Models\HistoryAtk;

class HistoryAtkController extends Controller
{
    /**
     * Tampilkan semua log history ATK
     */
    public function index()
    {
        $history = HistoryAtk::with(['admin.dataDiri', 'alat'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }

    /**
     * Tampilkan log history berdasarkan ID ATK (alat)
     */
    public function historyByAlat($id_alat)
    {
        $history = HistoryAtk::with('admin')
            ->where('id_alat_fk', $id_alat)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }

    /**
     * Tampilkan log history berdasarkan ID Admin (user)
     */
    public function historyByAdmin($id_admin)
    {
        $history = HistoryAtk::with('alat')
            ->where('id_admin_fk', $id_admin)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }

    /**
     * Tampilkan log history berdasarkan jenis aksi (tambah, hapus, manajemen_stock, pemakaian)
     */
    public function historyByAksi($jenis_aksi)
    {
        $history = HistoryAtk::with(['admin', 'alat'])
            ->where('jenis_aksi', $jenis_aksi)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }
}
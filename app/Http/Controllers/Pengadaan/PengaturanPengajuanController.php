<?php

namespace App\Http\Controllers\Pengadaan;

use App\Http\Controllers\Controller;
use App\Models\PengaturanPengajuan;
use Illuminate\Http\Request;

class PengaturanPengajuanController extends Controller
{
  public function status()
    {
        $setting = PengaturanPengajuan::latest()->first();

        return response()->json([
            'is_open' => $setting?->is_open ?? true,
            'tanggal_mulai' => $setting?->tanggal_mulai,
            'tanggal_selesai' => $setting?->tanggal_selesai,
        ]);
    }
      public function update(Request $request)
    {
        $request->validate([
            'is_open' => 'required|boolean',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ]);

        $setting = PengaturanPengajuan::updateOrCreate(
            ['id' => PengaturanPengajuan::latest()->first()?->id],
            $request->only(['is_open', 'tanggal_mulai', 'tanggal_selesai'])
        );

        return response()->json([
            'message' => 'Pengaturan berhasil diperbarui.',
            'data' => $setting
        ]);
    }
}

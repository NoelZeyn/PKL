<?php

namespace App\Http\Controllers\BA;

use App\Http\Controllers\Controller;
use App\Models\RequestPengadaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class BeritaAcaraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $beritaAcara = RequestPengadaan::with(['alat', 'user.dataDiri', 'user.penempatan'])
                ->where('status', 'done')
                ->where('id_users_fk', $user->id)
                ->orderBy('tanggal_permintaan', 'desc')
                ->get();
            return response()->json([
                'status' => 'success',
                'data' => $beritaAcara
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function exportPDF()
    {
        $user = Auth::user();

        // Ambil data request milik user yg statusnya done
        $pengajuan = RequestPengadaan::with(['alat', 'user.dataDiri', 'user.penempatan'])
            ->where('status', 'done')
            ->where('id_users_fk', $user->id)
            ->orderBy('tanggal_permintaan', 'desc')
            ->get();

        $pdf = Pdf::loadView('berita_acara', [
            'pengajuan' => $pengajuan,
            'user' => $user
        ])->setPaper('a4', 'portrait');

        return $pdf->download('berita-acara-' . now()->format('Ymd') . '.pdf');
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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

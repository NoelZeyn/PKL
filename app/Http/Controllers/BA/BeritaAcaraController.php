<?php

namespace App\Http\Controllers\BA;

use App\Http\Controllers\Controller;
use App\Models\Admin;
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

    public function exportPDFByUser($userId)
    {
        $pengajuan = RequestPengadaan::with(['alat', 'user.dataDiri', 'user.penempatan'])
            ->where('status', 'done')
            ->where('id_users_fk', $userId)
            ->orderBy('tanggal_permintaan', 'desc')
            ->get();

        $targetUser = Admin::with(['dataDiri', 'penempatan'])->findOrFail($userId);

        $pdf = Pdf::loadView('berita_acara', [
            'pengajuan' => $pengajuan,
            'user' => $targetUser,
        ])->setPaper('a4', 'portrait');

return $pdf->download(
    'berita-acara-' . (optional($targetUser->dataDiri)->nama_lengkap ?? $targetUser->NID) . '-' . now()->format('Ymd') . '.pdf'
);

    }public function listActiveUsers($id_penempatan_fk)
{
    try {
        $accounts = Admin::with('dataDiri')
            ->where('access', 'active')
            ->where('id_penempatan_fk', $id_penempatan_fk)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $accounts
        ], 200);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve accounts',
            'error' => $th->getMessage()
        ], 500);
    }
}



    public function indexByUser($id)
{
    try {
        $beritaAcara = RequestPengadaan::with(['alat', 'user.dataDiri', 'user.penempatan'])
            ->where('status', 'done')
            ->where('id_users_fk', $id)
            ->orderBy('tanggal_permintaan', 'desc')
            ->get();
        return response()->json([
            'status' => 'success',
            'data' => $beritaAcara
        ]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
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

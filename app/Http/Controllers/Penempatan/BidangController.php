<?php

namespace App\Http\Controllers\Penempatan;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use Illuminate\Http\Request;

class BidangController extends Controller
{
    public function index()
    {
        try {
            $bidang = Bidang::select('id', 'nama_bidang')->get();

            return response()->json([
                'status' => 'success',
                'data' => $bidang,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data bidang.',
            ], 500);
        }
    }
}

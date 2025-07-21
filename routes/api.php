<?php

use App\Http\Controllers\BA\BeritaAcaraController;
use App\Http\Controllers\Inventoris\AlatPenempatanController;
use App\Http\Controllers\Authentication\AccountController;
use App\Http\Controllers\Authentication\AdminController;
use App\Http\Controllers\Authentication\ApprovalController;
use App\Http\Controllers\Inventoris\AlatController;
use App\Http\Controllers\Inventoris\HistoryAtkController;
use App\Http\Controllers\Inventoris\HistoryPemakaianController;
use App\Http\Controllers\Inventoris\RequestATKBaruController;
use App\Http\Controllers\Penempatan\BidangController;
use App\Http\Controllers\Penempatan\PenempatanController;
use App\Http\Controllers\Authentication\ProfileController;
use App\Http\Controllers\Pengadaan\HistoryApprovalController;
use App\Http\Controllers\Pengadaan\KategoriPengadaanController;
use App\Http\Controllers\Pengadaan\PengajuanIntiController;
use App\Http\Controllers\Pengadaan\PengaturanPengajuanController;
use App\Http\Controllers\Pengadaan\RequestController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['middleware' => 'api'], function ($router) {
    Route::post('register', [AdminController::class, 'register']);
    Route::post('login', [AdminController::class, 'login']);
    Route::post('logout', [AdminController::class, 'logout']);
    Route::post('refresh', [AdminController::class, 'refresh']);
    Route::post('me', [AdminController::class, 'me']);
});



Route::group(['middleware' => 'api'], function () {
    Route::apiResource('penempatan', PenempatanController::class);
    Route::get('bidang', [BidangController::class, 'index']);
    Route::get('/penempatan/by-bidang/{id}', [PenempatanController::class, 'getByBidang']);

    Route::get('asman', [PengajuanIntiController::class, 'asman'])->middleware(['api', RoleMiddleware::class . ':superadmin,asman']);
    Route::patch('asman/approve', [PengajuanIntiController::class, 'approveAsman'])->middleware(['api', RoleMiddleware::class . ':superadmin,asman']);
    Route::patch('asman/reject', [PengajuanIntiController::class, 'rejectAsman'])->middleware(['api', RoleMiddleware::class . ':superadmin,asman']);
    Route::get('asman-all', [PengajuanIntiController::class, 'asmanAll'])->middleware(['api', RoleMiddleware::class . ':superadmin,asman']);

    Route::get('manajer', [PengajuanIntiController::class, 'manajer'])->middleware(['api', RoleMiddleware::class . ':superadmin,manajer']);
    Route::patch('manajer/approve', [PengajuanIntiController::class, 'approveManajer'])->middleware(['api', RoleMiddleware::class . ':superadmin,manajer']);
    Route::patch('manajer/reject', [PengajuanIntiController::class, 'rejectManajer'])->middleware(['api', RoleMiddleware::class . ':superadmin,manajer']);
    Route::get('manajer-all', [PengajuanIntiController::class, 'manajerAll'])->middleware(['api', RoleMiddleware::class . ':superadmin,manajer']);

    // Route::get('anggaran-all', [PengajuanIntiController::class, 'anggaranAll']);
    Route::get('anggaran', [PengajuanIntiController::class, 'anggaranAll'])->middleware(['api', RoleMiddleware::class . ':superadmin,anggaran']);
    Route::patch('anggaran/approve', [PengajuanIntiController::class, 'approveAnggaran'])->middleware(['api', RoleMiddleware::class . ':superadmin,anggaran']);
    Route::patch('anggaran/reject', [PengajuanIntiController::class, 'rejectAnggaran'])->middleware(['api', RoleMiddleware::class . ':superadmin,anggaran']);

    // atur nanti
    Route::get('admin', [PengajuanIntiController::class, 'pengajuanAdminTable']);
    Route::get('adminTahun', [PengajuanIntiController::class, 'pengajuanAdminTableTahun']);
    Route::get('adminSemester', [PengajuanIntiController::class, 'pengajuanAdminTableBySemester']);
    Route::patch('update-status', [PengajuanIntiController::class, 'updateStatus']);
    Route::apiResource('account', AccountController::class)->middleware(['api', RoleMiddleware::class . ':superadmin']);

    Route::get('pengaturan-pengajuan', [PengaturanPengajuanController::class, 'status'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin']);
    Route::post('pengaturan-pengajuan', [PengaturanPengajuanController::class, 'update'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin']);
});

Route::group(['middleware' => 'api'], function ($router) {
    Route::get('profile', [ProfileController::class, 'showProfile']);
    Route::put('profile', [ProfileController::class, 'updateProfile']);
    Route::post('editPassword', [ProfileController::class, 'updatePassword']);
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('alat', [AlatController::class, 'index']);
    Route::get('alat/{id}', [AlatController::class, 'show']);
    Route::post('alat', [AlatController::class, 'store'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin,anggaran,user,asman,manajer']);
    Route::put('alat/{id}', [AlatController::class, 'update'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin']);
    Route::delete('alat/{id}', [AlatController::class, 'destroy'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin']);
    Route::apiResource('history_pemakaian', HistoryPemakaianController::class);
    Route::post('/history_pemakaian_multi', [HistoryPemakaianController::class, 'storeMultiple']);

    Route::get('/alat-penempatan', [AlatPenempatanController::class, 'index']);
    Route::get('/alat-penempatan/{id_penempatan}', [AlatPenempatanController::class, 'getAlatByPenempatan']);
    Route::put('/alat-penempatan/{id_alat}/{id_penempatan}', [AlatPenempatanController::class, 'updateAlatPenempatan']);
    Route::get('/alat-terdistribusi', [AlatPenempatanController::class, 'getDistribusiAlat']);
    Route::post('/alat-penempatan', [AlatPenempatanController::class, 'store']);
    Route::patch('/alat-penempatan/stok', [AlatPenempatanController::class, 'updateStok']);
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('request', [RequestController::class, 'index']);
    Route::get('requestTahun', [RequestController::class, 'pengajuanPerByTahun']);
    Route::get('requestSemester', [RequestController::class, 'pengajuanPerSemesterByTahun']);
    Route::get('request/filter', [RequestController::class, 'getByPenempatan']);
    Route::get('showProfile', [ProfileController::class, 'showProfile']);
    Route::get('request/{id}', [RequestController::class, 'show']);
    Route::post('request', [RequestController::class, 'store'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin,user,anggaran']);
    Route::post('request-multiple', [RequestController::class, 'storeMultiple'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin,user,anggaran,asman,manajer']);
    // Route::put('request/{id}', [RequestController::class, 'update'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin,user,anggaran']);
    Route::delete('request/{id}', [RequestController::class, 'destroy'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin']);
});
Route::group(['middleware' => 'api'], function () {
    // Route::apiResource('alat', AlatController::class)->middleware(['api', RoleMiddleware::class.':superadmin']);
    Route::get('/pengajuan-baru', [RequestATKBaruController::class, 'index']);
    Route::post('/pengajuan-baru', [RequestATKBaruController::class, 'store']);
    Route::delete('/pengajuan-baru/{id}', [RequestATKBaruController::class, 'destroy'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin']);
    Route::patch('pengajuan-baru/approve/{id}', [RequestATKBaruController::class, 'approve'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin']);
    Route::patch('pengajuan-baru/reject/{id}', [RequestATKBaruController::class, 'reject'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin']);

    Route::apiResource('kategori_pengadaan', KategoriPengadaanController::class);
    Route::apiResource('berita-acara', BeritaAcaraController::class);
    Route::get('/export-pdf', [BeritaAcaraController::class, 'exportPDF']);
    Route::get('/list-user', [BeritaAcaraController::class, 'listActiveUsers']);
    Route::get('/berita-acara/user/{id}', [BeritaAcaraController::class, 'indexByUser']);
    Route::get('/export-pdf/{id}', [BeritaAcaraController::class, 'exportPDFByUser']);


    Route::apiResource('history_pemakaian', HistoryPemakaianController::class)->middleware(['api', RoleMiddleware::class . ':superadmin,admin,anggaran,asman,manajer,user_review']);
    Route::apiResource('history_approval', HistoryApprovalController::class)->middleware(['api', RoleMiddleware::class . ':superadmin,admin,anggaran,asman,manajer,user_review']);
    // Route::apiResource('request', RequestController::class)->middleware(['api', RoleMiddleware::class . ':superadmin,admin,user,anggaran']);
});

Route::group(['middleware' => 'api'], function () {
    Route::get('history-atk', [HistoryAtkController::class, 'index'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin,anggaran,asman,manajer,user_review']);
    Route::get('history-atk-alat/{id}', [HistoryAtkController::class, 'historyByAlat'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin,anggaran,asman,manajer,user_review']);
    Route::get('history-atk-admin/{id}', [HistoryAtkController::class, 'historyByAdmin'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin,anggaran,asman,manajer,user_review']);
});

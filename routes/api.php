<?php

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
use App\Http\Controllers\Pengadaan\PengajuanController;
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
    Route::get('/asman', [PengajuanController::class, 'asman']);
    Route::patch('/asman/approve', [PengajuanController::class, 'approveAsman']);
    Route::patch('/asman/reject', [PengajuanController::class, 'rejectAsman']);
    Route::get('asman-all', [PengajuanController::class, 'asmanAll']);

    Route::get('manajer', [PengajuanController::class, 'manajer']);
    Route::patch('/manajer/approve', [PengajuanController::class, 'approveManajer']);
    Route::patch('/manajer/reject', [PengajuanController::class, 'rejectManajer']);
    Route::get('manajer-all', [PengajuanController::class, 'manajerAll']);

    // Route::get('anggaran-all', [PengajuanController::class, 'anggaranAll']);
    Route::get('anggaran', [PengajuanController::class, 'anggaranAll']);
    Route::patch('/anggaran/approve', [PengajuanController::class, 'approveAnggaran']);
    Route::patch('/anggaran/reject', [PengajuanController::class, 'rejectAnggaran']);
    
    Route::get('admin', [PengajuanController::class, 'pengajuanAdminTable']);
    Route::apiResource('account', AccountController::class)->middleware(['api', RoleMiddleware::class . ':superadmin']);
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
    Route::put('alat/{id}', [AlatController::class, 'update']);
    Route::delete('alat/{id}', [AlatController::class, 'destroy'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin']);
    Route::apiResource('history_pemakaian', HistoryPemakaianController::class);
    Route::post('/history_pemakaian_multi', [HistoryPemakaianController::class, 'storeMultiple']);
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('request', [RequestController::class, 'index']);
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
    Route::delete('/pengajuan-baru/{id}', [RequestATKBaruController::class, 'destroy']);
    Route::patch('pengajuan-baru/approve/{id}', [RequestATKBaruController::class, 'approve']);
    Route::patch('pengajuan-baru/reject/{id}', [RequestATKBaruController::class, 'reject']);


    Route::apiResource('kategori_pengadaan', KategoriPengadaanController::class);
    Route::apiResource('history_pemakaian', HistoryPemakaianController::class)->middleware(['api', RoleMiddleware::class . ':superadmin,admin,anggaran,asman,manajer,user_review']);
    Route::apiResource('history_approval', HistoryApprovalController::class)->middleware(['api', RoleMiddleware::class . ':superadmin,admin,anggaran,asman,manajer,user_review']);
    // Route::apiResource('request', RequestController::class)->middleware(['api', RoleMiddleware::class . ':superadmin,admin,user,anggaran']);
});

Route::group(['middleware' => 'api'], function () {

    Route::get('history-atk', [HistoryAtkController::class, 'index'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin,anggaran,asman,manajer,user_review']);
    Route::get('history-atk-alat/{id}', [HistoryAtkController::class, 'historyByAlat'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin,anggaran,asman,manajer,user_review']);
    Route::get('history-atk-admin/{id}', [HistoryAtkController::class, 'historyByAdmin'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin,anggaran,asman,manajer,user_review']);
});

<?php

use App\Http\Controllers\Authentication\AccountController;
use App\Http\Controllers\Authentication\AdminController;
use App\Http\Controllers\Authentication\ApprovalController;
use App\Http\Controllers\Inventoris\AlatController;
use App\Http\Controllers\Inventoris\HistoryPemakaianController;
use App\Http\Controllers\Penempatan\PenempatanController;
use App\Http\Controllers\Authentication\ProfileController;
use App\Http\Controllers\Pengadaan\HistoryApprovalController;
use App\Http\Controllers\Pengadaan\KategoriPengadaanController;
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
    Route::post('alat', [AlatController::class, 'store'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin']);
    Route::put('alat/{id}', [AlatController::class, 'update']);
    Route::delete('alat/{id}', [AlatController::class, 'destroy'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin']);
    Route::apiResource('history_pemakaian', HistoryPemakaianController::class);
    Route::post('/history_pemakaian_multi', [HistoryPemakaianController::class, 'storeMultiple']);
    Route::get('approval1', [ApprovalController::class, 'approval1']);
    Route::put('editApproval1/{id}', [ApprovalController::class, 'editApproval1']);
    Route::get('approval2', [ApprovalController::class, 'approval2']);
    Route::put('editApproval2/{id}', [ApprovalController::class, 'editApproval2']);
    Route::get('approval3', [ApprovalController::class, 'approval3']);
    Route::put('editApproval3/{id}', [ApprovalController::class, 'editApproval3']);
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('request', [RequestController::class, 'index']);
    Route::get('showProfile', [ProfileController::class, 'showProfile']);
    Route::get('request/{id}', [RequestController::class, 'show']);
    Route::post('request', [RequestController::class, 'store'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin,user,anggaran']);
    // Route::put('request/{id}', [RequestController::class, 'update'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin,user,anggaran']);
    Route::delete('request/{id}', [RequestController::class, 'destroy'])->middleware(['api', RoleMiddleware::class . ':superadmin,admin']);
});
Route::group(['middleware' => 'api'], function () {
    // Route::apiResource('alat', AlatController::class)->middleware(['api', RoleMiddleware::class.':superadmin']);
    Route::apiResource('kategori_pengadaan', KategoriPengadaanController::class);
    Route::apiResource('history_pemakaian', HistoryPemakaianController::class)->middleware(['api', RoleMiddleware::class . ':superadmin,admin,user,anggaran']);
    Route::apiResource('history_approval', HistoryApprovalController::class)->middleware(['api', RoleMiddleware::class . ':superadmin,admin,user,anggaran']);
    // Route::apiResource('request', RequestController::class)->middleware(['api', RoleMiddleware::class . ':superadmin,admin,user,anggaran']);
});


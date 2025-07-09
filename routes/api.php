<?php

use App\Http\Controllers\Authentication\AccountController;
use App\Http\Controllers\Authentication\AdminController;
use App\Http\Controllers\Inventoris\AlatController;
use App\Http\Controllers\Penempatan\PenempatanController;
use App\Http\Controllers\Authentication\ProfileController;
use App\Http\Controllers\Pengadaan\KategoriPengadaanController;
use App\Http\Controllers\Pengadaan\RequestController;
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
    Route::apiResource('account', AccountController::class);
});

Route::group(['middleware' => 'api'], function ($router) {
    Route::get('profile', [ProfileController::class, 'showProfile']);
    Route::put('profile', [ProfileController::class, 'updateProfile']);
    Route::post('editPassword', [ProfileController::class, 'updatePassword']);
});

Route::group(['middleware' => 'api'], function () {
    Route::apiResource('alat', AlatController::class);
    Route::apiResource('kategori_pengadaan', KategoriPengadaanController::class);
    Route::apiResource('request', RequestController::class);
});

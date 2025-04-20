<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\AuthJWTController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KomentarController;
use App\Http\Controllers\TagArtikelController;

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware(['cors'])->group(function () {
    Route::post('/register', [AuthJWTController::class, 'register']);
    Route::post('/login', [AuthJWTController::class, 'login']);


    // Milik Artikel
    Route::get('/artikel', [ArtikelController::class, 'index']);
    Route::get('/artikel/trending', [ArtikelController::class, 'trending']);
    Route::get('/artikel/populer', [ArtikelController::class, 'populer']);
    Route::get('/artikel/{artikel}', [ArtikelController::class, 'show']);


    // Milik Kategori
    Route::get('/kategori', [KategoriController::class, 'index']);
    Route::get('/kategori/{kategori}', [KategoriController::class, 'show']);


    // Milik Komentar
    Route::get('/komentar', [KomentarController::class, 'index']);
    Route::get('/komentar/{komentar}', [KomentarController::class, 'show']);


    // Milik Tag
    Route::get('/tag', [TagController::class, 'index']);
    Route::get('/tag/{tag}', [TagController::class, 'show']);

    // Milik Get Penulis
    Route::get('/user/penulis', [UserController::class, 'getPenulis']);

    // Harus Memakai Login Bareer
    Route::middleware(['auth.jwt'])->group(function () {
        // Milik Me/Untuk Mendapatkan Data User Yang Aktif
        Route::post('/me', [AuthJWTController::class, 'me']);
        // Milik Logout
        Route::post('/logout', [AuthJWTController::class, 'logout']);

        // Routes untuk Artikel
        Route::post('/artikel', [ArtikelController::class, 'store']);
        Route::put('/artikel/{artikel}', [ArtikelController::class, 'update']);
        Route::delete('/artikel/{artikel}', [ArtikelController::class, 'destroy']);

        // Routes untuk Kategori
        Route::post('/kategori', [KategoriController::class, 'store']);
        Route::put('/kategori/{kategori}', [KategoriController::class, 'update']);
        Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy']);

        // Routes untuk Komentar
        Route::post('/komentar', [KomentarController::class, 'store']);
        Route::put('/komentar/{komentar}', [KomentarController::class, 'update']);
        Route::delete('/komentar/{komentar}', [KomentarController::class, 'destroy']);

        // Routes untuk Tag
        Route::post('/tag', [TagController::class, 'store']);
        Route::put('/tag/{tag}', [TagController::class, 'update']);
        Route::delete('/tag/{tag}', [TagController::class, 'destroy']);

        Route::put('/user/{user}', [UserController::class, 'update']);

        // Harus Admin
        Route::middleware(['cekRole:admin'])->group(function () {
            // Routes untuk User
            Route::get('/user', [UserController::class, 'index']);
            Route::post('/user', [UserController::class, 'store']);
            Route::get('/user/{user}', [UserController::class, 'show']);
            Route::delete('/user/{user}', [UserController::class, 'destroy']);

            // Routes untuk Role
            Route::get('/role', [RoleController::class, 'index']);
            Route::post('/role', [RoleController::class, 'store']);
            Route::get('/role/{role}', [RoleController::class, 'show']);
            Route::put('/role/{role}', [RoleController::class, 'update']);
            Route::delete('/role/{role}', [RoleController::class, 'destroy']);
        });

    });
});


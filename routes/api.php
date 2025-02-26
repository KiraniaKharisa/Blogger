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

Route::post('/register', [AuthJWTController::class, 'register']);
Route::post('/login', [AuthJWTController::class, 'login']);


// Milik Artikel
Route::get('/artikel', [ArtikelController::class, 'index']);
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

// Harus Memakai Login Bareer
Route::middleware(['auth.jwt'])->group(function () {
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

    // Harus Admin
    Route::middleware(['cekRole:admin'])->group(function () {
        // Routes untuk User
        Route::get('/user', [UserController::class, 'index']);
        Route::post('/user', [UserController::class, 'store']);
        Route::get('/user/{user}', [UserController::class, 'show']);
        Route::put('/user/{user}', [UserController::class, 'update']);
        Route::delete('/user/{user}', [UserController::class, 'destroy']);

        // Routes untuk Role
        Route::get('/role', [RoleController::class, 'index']);
        Route::post('/role', [RoleController::class, 'store']);
        Route::get('/role/{role}', [RoleController::class, 'show']);
        Route::put('/role/{role}', [RoleController::class, 'update']);
        Route::delete('/role/{role}', [RoleController::class, 'destroy']);
    });

});

// Routes untuk TagArtikel : Jika Dipake Uncomment Aja
// Route::get('/tag_artikel', [TagArtikelController::class, 'index']);
// Route::post('/tag_artikel', [TagArtikelController::class, 'store']);
// Route::get('/tag_artikel/{tag_artikel}', [TagArtikelController::class, 'show']);
// Route::put('/tag_artikel/{tag_artikel}', [TagArtikelController::class, 'update']);
// Route::delete('/tag_artikel/{tag_artikel}', [TagArtikelController::class, 'destroy']);


// Route::resource('/user', UserController::class)->except(['create', 'edit'])->middleware(['auth.jwt', 'cekRole:admin']);

// Route::resource('/artikel', ArtikelController::class)->except(['create', 'edit']);

// Route::resource('/kategori', KategoriController::class)->except(['create', 'edit']);

// Route::resource('/komentar', KomentarController::class)->except(['create', 'edit']);

// Route::resource('/role', RoleController::class)->except(['create', 'edit']);

// Route::resource('/tag', TagController::class)->except(['create', 'edit']);

// Route::resource('/tag_artikel', TagArtikelController::class)->except(['create', 'edit']);
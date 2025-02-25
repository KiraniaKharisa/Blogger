<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KomentarController;
use App\Http\Controllers\TagArtikelController;

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::resource('/user', UserController::class)->except(['create', 'edit']);

Route::resource('/artikel', ArtikelController::class)->except(['create', 'edit']);

Route::resource('/kategori', KategoriController::class)->except(['create', 'edit']);

Route::resource('/komentar', KomentarController::class)->except(['create', 'edit']);

Route::resource('/role', RoleController::class)->except(['create', 'edit']);

Route::resource('/tag', TagController::class)->except(['create', 'edit']);

Route::resource('/tag_artikel', TagArtikelController::class)->except(['create', 'edit']);
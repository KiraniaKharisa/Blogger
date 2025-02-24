<?php

namespace App\Http\Controllers;

use App\Models\Komentar;
use Illuminate\Http\Request;

class KomentarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $komentars = Komentar::with(['user', 'artikel'])->get();

        if($komentars->isEmpty()){        
            return response()->json([
            'success' => true,
            'data' => 'Data Komentar Kosong',
            ], 200);
        
        } else {
            return response()->json([
            'success' => true,
            'data' => $komentars,
            ], 200);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'komentar' => 'required|min:150',
        ],[
            'komentar.required' => 'Komentar Tidak boleh kosong',
            'komentar.max' => 'Komentar Minimal 150 karakter'
        ]);

        // jika berhasil masukkan datanya ke database
        $komentarBuat = Kategori::create($validate);

        if ($komentarBuat) {
            return response()->json([
                'success' => true,
                'pesan' => 'Data Berhasil Ditambahkan',
                'data' => $komentarBuat,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'pesan' => 'Data Gagal Ditambahkan'
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $komentar = Komentar::with(['user', 'artikel'])->find($id);

        if(is_null($komentar)) {
            return response()->json([
                'success' => true,
                'data' => 'Data Komentar Kosong',
            ], 200);
        } else {
            return response()->json([
                'success' => true,
                'data' => $komentar,
            ], 200);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
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
        // cari komentar berdasarkan id
        $komentar = Komentar::find($id);

        if(is_null($komentar)) {
            // kembalikan response sukses
            return response()->json([
                'success' => false,
                'pesan' => 'Data Komentar Tidak Ada',
            ], 404);
        }

        // hapus komentar
        $deleteKomentar = $komentar->delete();

        if($deleteKomentar) {
            return response()->json([
                'success' => true,
                'pesan' => 'Komentar Berhasil Dihapus',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'pesan' => 'Komentar Gagal Dihapus',
            ], 400);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kategoris = Kategori::with('artikels')->get();

        if($kategoris->isEmpty()){
        
        return response()->json([
        'success' => true,
        'data' => 'Data Kategori Kosong',
        ], 404);
        
        } else {
        return response()->json([
        'success' => true,
        'data' => $kategoris,
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
            'kategori' => 'required|unique:kategoris', 
        ],[
            // masukkan pesan error kamu di sini
            'kategori.required' => 'Kolom Kategori Harus Diisi',
            'kategori.unique' => 'Kolom Kategori Sudah Di Pakai',
        ]);

        // jika berhasil masukkan datanya ke database
        $kategoriBuat = Kategori::create($validate);

        if($kategoriBuat) {
            // kirimkan pesan berhasil 
            return response()->json([ 
                'success' => true,
                'pesan' => 'Data Berhasil Ditambahkan',
                'data' => $kategoriBuat,
            ], 200);
        } else {
            // kirimkan pesan gagal 
            return response()->json([
                'success' => false,
                'pesan' => 'Data Gagal Ditambahkan',           
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $kategori = Kategori::with('artikels')->find($id);

        if(is_null($kategori)) {
            return response()->json([
                'success' => true,
                'data' => 'Data Kategori Kosong',
            ], 404);
        } else{
            return response()->json([
                'success' => true,
                'data' => $kategori,
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
        // ambil kategori berdasarkan id
        $kategori = Kategori::find($id);

        if(is_null($kategori)) {
            // Kembalikan response sukses
            return response()->json([
                'success' => false,
                'pesan' => 'Data Kategori Tidak Ada',
            ], 404);
        }

        // Ambil hanya field yang ada di tabel kategori
        $validFields = array_intersect_key($request->all(), $kategori->getAttributes()); 

        // Jika tidak ada field yang cocok 
        if (empty($validFields)) { 
            // kirimkan pesan gagal 
            return response()->json([
                'success' => false,
                'pesan' => 'Tidak Ada Kolom Yang Cocok',           
            ], 400);
        }

        // validasi Kategori
        $validate = $request->validate([
            'kategori' => 'sometimes|required|unique:kategoris,kategori,'.$id,
        ],[  
            // masukkan pesan error kamu di sini
            'kategori.required' => 'Kolom Kategori Harus Diisi',
            'kategori.unique' => 'Kolom Kategori Sudah Di Pakai',
        ]);

        $kategoriUpdate = $kategori->update($validate); 

        if($kategoriUpdate) {
            // kirimkan pesan berhasil 
            return response()->json([
                'success' => true,
                'pesan' => 'Data Berhasil diupdate',
                'data' => $validate,
            ], 200);
        } else {
            // kirimkan pesan gagal 
            return response()->json([
                'success' => false,
                'pesan' => 'Data Gagal diupdate',           
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Cari Kategori berdasarkan ID
        $kategori = Kategori::find($id);

        if(is_null($kategori)) {
            // Kembalikan response sukses
            return response()->json([
                'success' => true,
                'pesan' => 'Data Kategori Tidak Ada',
            ], 404);
        }

        // Hapus Kategori
        $deleteKategori = $kategori->delete();

        if(!$deleteKategori) {
            // Kembalikan response sukses
            return response()->json([
                'success' => true,
                'pesan' => 'Kategori Berhasil Dihapus',
            ], 200);
        } else {
            // Jika Kategori tidak ditemukan
            return response()->json([
                'success' => false, 
                'pesan' => 'Kategori gagal Dihapus',
            ], 400);
        }
    }
}

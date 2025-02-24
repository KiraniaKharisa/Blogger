<?php

namespace App\Http\Controllers;

use App\Models\TagArtikel;
use Illuminate\Http\Request;

class TagArtikelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tag_artikels = TagArtikel::all();

        if($tag_artikels->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => 'Data Tag Artikel Kosong',
            ], 200);
        } else{
            return response()->json([
                'success' => true,
                'data' => $tag_artikels,
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
            'tag' => 'required|regex:/^#\S+$/|unique:tags', // 'regex:/^#\S+$/' untuk didepanya harus ada hastag
        ],[
            // masukkan pesan error kamu di sini
            'tag.required' => 'Kolom Tag Harus Diisi',
            'tag.regex' => 'Kolom Tag Harus Berawalan #',
            'tag.unique' => 'Kolom Tag Sudah Di Pakai',
        ]);

        // jika berhasil masukkan datanya ke database
        $tagBuat = TagArtikel::create($validate);

        if($tagBuat) {
            // kirimkan pesan berhasil 
            return response()->json([
                'success' => true,
                'pesan' => 'Data Berhasil ditambahkan',
                'data' => $tagBuat,
            ], 200);
        } else {
            // kirimkan pesan gagal 
            return response()->json([
                'success' => false,
                'pesan' => 'Data Gagal ditambahkan',           
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tag_artikel = TagArtikel::find($id);

        if(is_null($tag_artikel)) {
            return response()->json([
                'success' => true,
                'data' => 'Data Tag Artikel Kosong',
            ], 200);
        } else{
            return response()->json([
                'success' => true,
                'data' => $tag_artikel,
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
        // ambil tag berdasarkan id
        $tag = TagArtikel::find($id);

        if(is_null($tag)) {
            // Kembalikan response sukses
            return response()->json([
                'success' => false,
                'pesan' => 'Data Tag Tidak Ada',
            ], 404);
        }

        // Ambil hanya field yang ada di tabel tag 
        $validFields = array_intersect_key($request->all(), $tag->getAttributes()); 

        // Jika tidak ada field yang cocok 
        if (empty($validFields)) { 
            // kirimkan pesan gagal 
            return response()->json([
                'success' => false,
                'pesan' => 'Tidak Ada Kolom Yang Cocok',           
            ], 400);
        }

        // validasi Tag
        $validate = $request->validate([
            'tag' => 'sometimes|required|regex:/^#\S+$/|unique:tags,tag,'.$id, // 'regex:/^#\S+$/' untuk didepanya harus ada hastag
        ],[  
            // masukkan pesan error kamu di sini
            'tag.required' => 'Kolom Tag Harus Diisi',
            'tag.regex' => 'Kolom Tag Harus Berawalan #',
            'tag.unique' => 'Kolom Tag Sudah Di Pakai',
        ]);

        $tagUpdate = $tag->update($validate); 

        if($tagUpdate) {
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
        // Cari tag berdasarkan ID
        $tag_artikel = TagArtikel::find($id);

        // Hapus tag
        $deleteTag = $tag_artikel->delete();

        if($deleteTag) {
            // Kembalikan response sukses
            return response()->json([
                'success' => true,
                'message' => 'Tag Artikel Berhasil Di Hapus',
            ], 200);
        } else {
            // Jika tag tidak ditemukan
            return response()->json([
                'success' => false, 
                'message' => 'Tag Artikel Tidak Di Temukan',
            ], 404);
        }
    }
}

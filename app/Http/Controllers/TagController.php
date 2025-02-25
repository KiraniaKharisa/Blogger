<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $tags = Tag::with('artikels')->get();

        $sort = $request->json('sort', 'created_at'); // Kolom Apa yang akan diurutkan 

        // ASC : Dari terkecil ke yang terbesar
        // DESC : Dari terbesar ke yang terkeci;
        // Jika kita gunakan di created_at amaka DESC adalah mengurutkan postingan yang paling baru
        $order = strtoupper($request->json('order', 'DESC')); 
        $start = $request->json('start', null); // Digunakan Untuk Pengambilan Data Mulai dari data keberapa
        $end = $request->json('end', null); // Digunakan untuk pengambilan data akhir jadi start sampai end
        $filters = $request->json('filters', []); // digunakan untuk mencari sesuai key dan field di database

        // Dapatkan daftar field yang valid dari tabel 'tags'
        $validColumns = Schema::getColumnListing('tags');

        // Validasi order (hanya ASC atau DESC)
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        // Validasi sort (jika tidak valid, gunakan default: created_at)
        if (!in_array($sort, $validColumns)) { 
            $sort = 'created_at';
        }

        // Query Tags dengan eager loading
        $query = Tag::with('artikels');

        // Apply filtering jika ada dan field valid
        if (!empty($filters)) {
            foreach ($filters as $field => $value) {
                if (in_array($field, $validColumns)) {
                    $query->where($field, 'LIKE', "%$value%");
                }
            }
        }

        // Apply sorting (hanya jika field valid)
        $query->orderBy($sort, $order);

        // Jika start dan end ada, gunakan paginasi manual
        if (!is_null($start) && !is_null($end)) {
            $query->skip($start)->take($end - $start);
        }

        // Ambil data
        $tags = $query->get();

        if($tags->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => 'Data Tag Kosong',
            ], 200);
        } else{
            return response()->json([
                'success' => true,
                'data' => $tags,
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
        $tagBuat = Tag::create($validate);

        if($tagBuat) {
            // kirimkan pesan berhasil 
            return response()->json([
                'success' => true,
                'pesan' => 'Data Berhasil ditambahkan',
                'data' => $validate,
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
        $tag = Tag::with('artikels')->find($id);

        if(is_null($tag)) {
            return response()->json([
                'success' => true,
                'data' => 'Data Tag Kosong',
            ], 404);
        } else{
            return response()->json([
                'success' => true,
                'data' => $tag,
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
        $tag = Tag::find($id);

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
        $tag = Tag::find($id);

        if(is_null($tag)) {
            // Kembalikan response sukses
            return response()->json([
                'success' => false,
                'pesan' => 'Data Tag Tidak Ada',
            ], 404);
        }

        // Hapus Tag Yang Berelasi Dengan Artikel Di Tabel TagArtikel
        $tag->artikels()->detach();
        // Hapus tag
        $deleteTag = $tag->delete();

        if($deleteTag) {
            // Kembalikan response sukses
            return response()->json([
                'success' => true,
                'pesan' => 'Tag Berhasil Dihapus',
            ], 200);
        } else {
            // Jika tag tidak ditemukan
            return response()->json([
                'success' => false, 
                'pesan' => 'Tag gagal Dihapus',
            ], 400);
        }
    }
}

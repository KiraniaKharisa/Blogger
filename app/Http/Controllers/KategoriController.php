<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $kategoris = Kategori::with('artikels')->get();

        $sort = $request->json('sort', 'created_at'); // Kolom Apa yang akan diurutkan 

        // ASC : Dari terkecil ke yang terbesar
        // DESC : Dari terbesar ke yang terkeci;
        // Jika kita gunakan di created_at amaka DESC adalah mengurutkan postingan yang paling baru
        $order = strtoupper($request->json('order', 'DESC')); 
        $start = $request->json('start', null); // Digunakan Untuk Pengambilan Data Mulai dari data keberapa
        $end = $request->json('end', null); // Digunakan untuk pengambilan data akhir jadi start sampai end
        $filters = $request->json('filters', []); // digunakan untuk mencari sesuai key dan field di database

        // Dapatkan daftar field yang valid dari tabel 'kategoris'
        $validColumns = Schema::getColumnListing('kategoris');

        // Validasi order (hanya ASC atau DESC)
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        // Validasi sort (jika tidak valid, gunakan default: created_at)
        if (!in_array($sort, $validColumns)) { 
            $sort = 'created_at';
        }

        // Query Kategori dengan eager loading
        $query = Kategori::with('artikels');

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
        $kategoris = $query->get();

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


        try {
            // $kategori->artikels()->delete();  // Jika disuruh menghapus kategori dan artikelnya harus dihapus aktifkan ini
            // Hapus Kategori
            $deleteKategori = $kategori->delete();

        } catch(\Exception $e) {

            return response()->json([
                'success' => false, 
                'pesan' => 'Kategori gagal Dihapus Dikarenakan Ada Data Yang Berelasi Dengannya',
            ], 400);
        }

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

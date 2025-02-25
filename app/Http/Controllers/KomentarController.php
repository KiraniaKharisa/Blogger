<?php

namespace App\Http\Controllers;


use App\Models\Komentar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class KomentarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $komentars = Komentar::with(['user', 'artikel'])->get();

        // Filter dan Search
        $sort = $request->json('sort', 'created_at'); // Kolom Apa yang akan diurutkan 

        // ASC : Dari terkecil ke yang terbesar
        // DESC : Dari terbesar ke yang terkeci;
        // Jika kita gunakan di created_at amaka DESC adalah mengurutkan postingan yang paling baru
        $order = strtoupper($request->json('order', 'DESC')); 
        $start = $request->json('start', null);
        $end = $request->json('end', null);
        $filters = $request->json('filters', []);

        // Dapatkan daftar field yang valid dari tabel 'komentars'
        $validColumns = Schema::getColumnListing('komentars');

        // Validasi order (hanya ASC atau DESC)
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        // Validasi sort (jika tidak valid, gunakan default: created_at)
        if (!in_array($sort, $validColumns)) { 
            $sort = 'created_at';
        }

        // Query Komentar dengan eager loading
        $query = Komentar::with(['user', 'artikel']);

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
        $komentars = $query->get();

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
            'komentar' => 'required',
            'artikel_id' => 'required|exists:artikels,id',
            'user_id' => 'required|exists:users,id',
        ],[
            'komentar.required' => 'Komentar Tidak boleh kosong',
            'artikel_id.required' => 'Id Artikel Tidak Boleh Kosong',
            'artikel_id.exists' => 'Id Artikel Yang Dimasukkan Tidak Ada Di Data Kami',
            'user_id.required' => 'Id User Tidak Boleh Kosong',
            'user_id.exists' => 'Id User Yang Dimasukkan Tidak Ada Di Data Kami',
        ]);

        // jika berhasil masukkan datanya ke database
        $komentarBuat = Komentar::create($validate);

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
            ], 404);
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
        
        $komentar = Komentar::find($id);
        
        if(is_null($komentar)) {
            return response()->json([
                'success' => true,
                'data' => 'Data Komentar Kosong',
            ], 404);
        } 
        
        // ambil hanya field yang ada di tabel Komentar
        $validFields = array_intersect_key($request->all(), $komentar->getAttributes()); 

        // Jika tidak ada field yang cocok 
        if (empty($validFields)) { 
            // kirimkan pesan gagal 
            return response()->json([
                'success' => false,
                'pesan' => 'Tidak Ada Kolom Yang Cocok',           
            ], 400);
        }
        
        $validate = $request->validate([
            'komentar' => 'required',
            'artikel_id' => 'required|exists:artikels,id',
            'user_id' => 'required|exists:users,id',
        ],[
            'komentar.required' => 'Komentar Tidak boleh kosong',
            'artikel_id.required' => 'Id Artikel Tidak Boleh Kosong',
            'artikel_id.exists' => 'Id Artikel Yang Dimasukkan Tidak Ada Di Data Kami',
            'user_id.required' => 'Id User Tidak Boleh Kosong',
            'user_id.exists' => 'Id User Yang Dimasukkan Tidak Ada Di Data Kami',
        ]);

        // jika berhasil masukkan datanya ke database
        $komentarEdit = $komentar->update($validate);

        if ($komentarEdit) {
            return response()->json([
                'success' => true,
                'pesan' => 'Data Berhasil Diedit',
                'data' => $komentar,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'pesan' => 'Data Gagal Diedit'
            ], 400);
        }
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

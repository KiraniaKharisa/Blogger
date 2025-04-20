<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $users = User::with(['komentars', 'artikels', 'role'])->get();

        $sort = $request->json('sort', 'created_at'); // Kolom Apa yang akan diurutkan 

        // ASC : Dari terkecil ke yang terbesar
        // DESC : Dari terbesar ke yang terkeci;
        // Jika kita gunakan di created_at amaka DESC adalah mengurutkan postingan yang paling baru
        $order = strtoupper($request->json('order', 'DESC')); 
        $start = $request->json('start', null); // Digunakan Untuk Pengambilan Data Mulai dari data keberapa
        $end = $request->json('end', null); // Digunakan untuk pengambilan data akhir jadi start sampai end
        $filters = $request->json('filters', []); // digunakan untuk mencari sesuai key dan field di database

        // Dapatkan daftar field yang valid dari tabel 'users'
        $validColumns = Schema::getColumnListing('users');

        // Validasi order (hanya ASC atau DESC)
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        // Validasi sort (jika tidak valid, gunakan default: created_at)
        if (!in_array($sort, $validColumns)) { 
            $sort = 'created_at';
        }

        // Query User dengan eager loading
        $query = User::with(['komentars', 'artikels', 'role']);

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
        $users = $query->get();

        if($users->isEmpty()){        
            return response()->json([
                'success' => false,
                'pesan' => 'Data User Kosong',
            ], 404);
            
            } else {
            return response()->json([
                'success' => true,
                'data' => $users,
            ], 200);
        }
    }

    public function getPenulis(Request $request) {
        // $users = User::with(['komentars', 'artikels', 'role'])->get();

        $sort = $request->json('sort', 'created_at'); // Kolom Apa yang akan diurutkan 

        // ASC : Dari terkecil ke yang terbesar
        // DESC : Dari terbesar ke yang terkeci;
        // Jika kita gunakan di created_at amaka DESC adalah mengurutkan postingan yang paling baru
        $order = strtoupper($request->json('order', 'DESC')); 
        $start = $request->json('start', null); // Digunakan Untuk Pengambilan Data Mulai dari data keberapa
        $end = $request->json('end', null); // Digunakan untuk pengambilan data akhir jadi start sampai end
        $filters = $request->json('filters', []); // digunakan untuk mencari sesuai key dan field di database

        // Dapatkan daftar field yang valid dari tabel 'users'
        $validColumns = Schema::getColumnListing('users');

        // Validasi order (hanya ASC atau DESC)
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        // Validasi sort (jika tidak valid, gunakan default: created_at)
        if (!in_array($sort, $validColumns)) { 
            $sort = 'created_at';
        }

        // Query User dengan eager loading
        $query = User::with(['artikels'])->select(['id', 'name']);

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
        $users = $query->get();

        if($users->isEmpty()){        
            return response()->json([
                'success' => false,
                'pesan' => 'Data User Kosong',
            ], 404);
            
            } else {
            return response()->json([
                'success' => true,
                'data' => $users,
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
            'name' => 'required|min:5|max:100',
            'email' => 'required|unique:users|email',
            'password' => 'required|min:8', 
            'role_id' => 'required|exists:roles,id',
            'profil' => [
                'required',
                'regex:/^data:image\/(png|jpeg|jpg|gif|webp);base64,([A-Za-z0-9+\/=]+)$/'
            ]
        ],[
            // masukkan pesan error kamu di sini
            'name.required' => 'Kolom Name Harus Diisi',
            'name.min' => 'Kolom name Minimal 5 kata',
            'name.max' => 'Kolom name Maksimal 100',
            'email.required' => 'Kolom Email Harus Diisi',
            'email.unique' => 'Email ini sudah terdaftar',
            'email.email' => 'Kolom Email Harus Berupa Email',
            'password.required' => 'Kolom Password Harus Diisi',
            'password.min' => 'Kolom Password Minimal 8 karakter',
            'role_id.required' => 'Role Id Harus Diisi',
            'role_id.exists' => 'Data Role Yang Bersangkutan Tidak Ada',
            'profil.required' => 'Profile Harus Diisi',
            'profil.regex' => 'Foto Harus Berbentuk PNG, JPEG, JPG, GIF, WEBP'
        ]);

        // Simpan Gambar
        // Cek gambar ada atau tidak
        if($request->has('profil')) {
            // Ambil Base64 Siman Sebagai Variabel
            $imageData = $validate['profil'];

            // Ekstrak ekstensi gambar dari data base64
            preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches);
            $extension = strtolower($matches[1] ?? 'png'); // Default ke 'png' jika tidak ditemukan

            // Konversi base 64 menjadi gambar
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $imageData = base64_decode($imageData);

            // Cek Apakah beneran base64 nya berupa gambar
            if(!$imageData && !@imagecreatefromstring($imageData)) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Banner Tidak Berupa Gambar',
                    ], 422);
            }

            // Save Image Ke Aplikasi public/image/profile
            $saveFile = $this->uploudImage($imageData, $extension, 'image/profile/');
            $validate['profil'] = $saveFile;
        } else {
            return response()->json([
                'success' => false,
                'pesan' => 'Profile Wajib Diisi',
                ], 422);
        }

        // Hash Password
        $validate['password'] = bcrypt($validate['password']);

        // jika berhasil masukkan datanya ke database
        $userBuat = User::create($validate);


        if($userBuat) {
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
        // temukan data berdasarkan id
        $user = User::with(['komentars', 'artikels', 'role'])->find($id);

        if(is_null($user)){    
            return response()->json([
                'success' => true,
                'data' => 'Data User Kosong',
            ], 404);
            
        } else {
            return response()->json([
                'success' => true,
                'data' => $user,
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
        $user = User::find($id);

        if(is_null($user)){    
            return response()->json([
                'success' => true,
                'data' => 'Data User Kosong',
            ], 404);
            
        }

        // Ambil hanya field yang ada di tabel user
        $validFields = array_intersect_key($request->all(), $user->getAttributes()); 

        // Jika tidak ada field yang cocok 
        if (empty($validFields)) { 
            // kirimkan pesan gagal 
            return response()->json([
                'success' => false,
                'pesan' => 'Tidak Ada Kolom Yang Cocok',           
            ], 400);
        }

        $validate = $request->validate([
            'name' => 'required|min:5|max:100',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
            'profil' => [
                'nullable',
                'regex:/^data:image\/(png|jpeg|jpg|gif|webp);base64,([A-Za-z0-9+\/=]+)$/'
            ]
        ],[
            // masukkan pesan error kamu di sini
            'name.required' => 'Kolom Name Harus Diisi',
            'name.min' => 'Kolom name Minimal 5 kata',
            'name.max' => 'Kolom name Maksimal 100',
            'email.required' => 'Kolom Email Harus Diisi',
            'email.unique' => 'Email ini sudah terdaftar',
            'email.email' => 'Kolom Email Harus Berupa Email',
            'role_id.required' => 'Role Id Harus Diisi',
            'role_id.exists' => 'Data Role Yang Bersangkutan Tidak Ada',
            'profil.required' => 'Profile Harus Diisi',
            'profil.regex' => 'Foto Harus Berbentuk PNG, JPEG, JPG, GIF, WEBP'
        ]);

        // Simpan Gambar
        // Cek gambar ada atau tidak
        if($request->has('profil') && $request->json('profil') != null) {
            // Ambil Base64 Siman Sebagai Variabel
            $imageData = $validate['profil'];

            // Ekstrak ekstensi gambar dari data base64
            preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches);
            $extension = strtolower($matches[1] ?? 'png'); // Default ke 'png' jika tidak ditemukan

            // Konversi base 64 menjadi gambar
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $imageData = base64_decode($imageData);

            // Cek Apakah beneran base64 nya berupa gambar
            if(!$imageData && !@imagecreatefromstring($imageData)) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Profile Tidak Berupa Gambar',
                    ], 422);
            }

            // Save Image Ke Aplikasi public/image/profile
            // Dan hapus image lamanya
            $saveFile = $this->uploudImage($imageData, $extension, 'image/profile/', $user->profil);
            
            // Cekk Error Uploud Image
            if(is_array($saveFile)) {
                if(!$saveFile['success']) {
                    return response()->json([
                        'success' => false,
                        'pesan' => 'Uploud Image Error',
                        ], 500);
                }
            }

            $validate['profil'] = $saveFile;
        }

        // jika berhasil masukkan datanya ke database
        $userEdit = $user->update($validate);

        if($userEdit) {
            // kirimkan pesan berhasil 
            return response()->json([
                'success' => true,
                'pesan' => 'Data Berhasil diedit',
                'data' => $validate,
            ], 200);
        } else {
            // kirimkan pesan gagal 
            return response()->json([
                'success' => false,
                'pesan' => 'Data Gagal diedit',           
            ], 400);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        
        if(is_null($user)){    
            return response()->json([
                'success' => true,
                'data' => 'Data User Kosong',
            ], 404);
            
        }

        // Hapus User
        try {
            // $user->artikels()->delete();  // Jika disuruh menghapus user dan artikelnya harus dihapus aktifkan ini
            // $user->komentars()->delete();  // Jika disuruh menghapus user dan artikelnya harus dihapus aktifkan ini
            $deleteUser = $user->delete();

        } catch(\Exception $e) {

            return response()->json([
                'success' => false, 
                'pesan' => 'User gagal Dihapus Dikarenakan Ada Data Yang Berelasi Dengannya',
            ], 400);
        }

        if($deleteUser) {
            $profilUserDelete = $this->deleteImage($user->profil);
            if(!$profilUserDelete) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Hapus Image Error',
                    ], 500);
            }

            // Kembalikan response sukses  
            return response()->json([
                'success' => true,
                'pesan' => 'User Berhasil Dihapus',
            ], 200);
        } else {
            // Jika Artikel tidak ditemukan
            return response()->json([
                'success' => false, 
                'pesan' => 'User gagal Dihapus',
            ], 400);
        }
    }
}

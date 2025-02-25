<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $roles = Role::with('users')->get();
        // Filter dan Search
        $sort = $request->json('sort', 'created_at'); // Kolom Apa yang akan diurutkan 

        // ASC : Dari terkecil ke yang terbesar
        // DESC : Dari terbesar ke yang terkeci;
        // Jika kita gunakan di created_at amaka DESC adalah mengurutkan postingan yang paling baru
        $order = strtoupper($request->json('order', 'DESC')); 
        $start = $request->json('start', null); // Digunakan Untuk Pengambilan Data Mulai dari data keberapa
        $end = $request->json('end', null); // Digunakan untuk pengambilan data akhir jadi start sampai end
        $filters = $request->json('filters', []); // digunakan untuk mencari sesuai key dan field di database

        // Dapatkan daftar field yang valid dari tabel 'roles'
        $validColumns = Schema::getColumnListing('roles');

        // Validasi order (hanya ASC atau DESC)
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        // Validasi sort (jika tidak valid, gunakan default: created_at)
        if (!in_array($sort, $validColumns)) { 
            $sort = 'created_at';
        }

        // Query Role dengan eager loading
        $query = Role::with('users');

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
        $roles = $query->get();

        if($roles->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => 'Data Role Kosong',
            ], 200);

        } else{
            return response()->json([
                'success' => true,
                'data' => $roles,
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
            'role' => 'required|unique:roles', 
        ],[
            // masukkan pesan error kamu di sini
            'role.required' => 'Kolom Role Harus Diisi',
            'role.unique' => 'Kolom Role Sudah Di Pakai',
        ]);

        // jika berhasil masukkan datanya ke database
        $roleBuat = Role::create($validate);

        if($roleBuat) {
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
        $role = Role::with('users')->find($id);

        if(is_null($role)) {
            return response()->json([
                'success' => true,
                'data' => 'Data Role Kosong',
            ], 404);
        } else{
            return response()->json([
                'success' => true,
                'data' => $role,
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
        // ambil role berdasarkan id
        $role = Role::find($id);

        if(is_null($role)) {
            // Kembalikan response sukses
            return response()->json([
                'success' => false,
                'pesan' => 'Data Role Tidak Ada',
            ], 404);
        }

        // Ambil hanya field yang ada di tabel Role 
        $validFields = array_intersect_key($request->all(), $role->getAttributes()); 

        // Jika tidak ada field yang cocok 
        if (empty($validFields)) { 
            // kirimkan pesan gagal 
            return response()->json([
                'success' => false,
                'pesan' => 'Tidak Ada Kolom Yang Cocok',           
            ], 400);
        }

        // validasi Role
        $validate = $request->validate([
            'role' => 'sometimes|required|unique:roles,role,'.$id, 
        ],[  
            // masukkan pesan error kamu di sini
            'role.required' => 'Kolom Role Harus Diisi',
            'role.unique' => 'Kolom Role Sudah Di Pakai',
        ]);

        $roleUpdate = $role->update($validate); 

        if($roleUpdate) {
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
        // Cari Role berdasarkan ID
        $role = Role::find($id);

        if(is_null($role)) {
            // Kembalikan response sukses
            return response()->json([
                'success' => true,
                'pesan' => 'Data Role Tidak Ada',
            ], 404);
        }

        try {
            // $role->users()->delete();  // Jika disuruh menghapus role dan usernya juga harus dihapus aktifkan ini
            // Hapus role
            $deleteRole = $role->delete();

        } catch(\Exception $e) {

            return response()->json([
                'success' => false, 
                'pesan' => 'Role gagal Dihapus Dikarenakan Ada Data Yang Berelasi Dengannya',
            ], 400);
        }

        

        if($deleteRole) {
            // Kembalikan response sukses
            return response()->json([
                'success' => true,
                'pesan' => 'Role Berhasil Dihapus',
            ], 200);
        } else {
            // Jika Role tidak ditemukan
            return response()->json([
                'success' => false, 
                'pesan' => 'Role gagal Dihapus',
            ], 400);
        }
    }
}

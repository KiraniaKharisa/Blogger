<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;

class AuthJWTController extends Controller
{

    public function register(Request $request) {
        $validate = $request->validate([
            'name' => 'required|min:5|max:100',
            'email' => 'required|unique:users|email',
            'password' => 'required|min:8', 
            'profil' => [
                'nullable',
                'regex:/^data:image\/(png|jpeg|jpg|gif|webp|svg);base64,([A-Za-z0-9+\/=]+)$/'
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
            'profil.regex' => 'Foto Harus Berbentuk PNG, JPEG, JPG, GIF, WEBP'
        ]);

        // Jika ia tidak mengisi profile maka berikan profile default base64
        $validate['profil'] = $request->json('profil', env('DEFAULT_PROFIL'));
        $validate['role_id'] = 1; // default register itu role nya penulis

        // Simpan Gambar
        // Cek gambar ada atau tidak
        if($validate['profil']) {
            // Ambil Base64 Siman Sebagai Variabel
            $imageData = $validate['profil'];

            // Ekstrak ekstensi gambar dari data base64
            // Preg Match : itu fungsi yang digunakan untuk melakukan pencarian pola (pattern matching) dalam sebuah string menggunakan Regular Expression (RegEx).
            // Berarti yang ia cari adalah w+ yang ditengah, w+ adalah word atau kata, dia mencari ekstensi gambar untuk disimpan nanti sesuai ekstensi
            preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches); // ambil ekstensi dari gambar base64
            dd($matches);
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
                'pesan' => 'Register Berhasil Silahkan Login',
                'data' => $validate,
            ], 200);
        } else {
            // kirimkan pesan gagal 
            return response()->json([
                'success' => false,
                'pesan' => 'Register Gagal',           
            ], 500);
        }
    }
   
    public function login(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ],[
            // masukkan pesan error kamu di sini
            'email.required' => 'Kolom Email Harus Diisi',
            'email.email' => 'Kolom Email Harus Berupa Email',
            'password.required' => 'Kolom Password Harus Diisi', 
        ]);

        // $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($validate)) {
            return response()->json([
                'status' => false,
                'pesan' => 'Data Salah Tidak Teregistrasi',
            ], 401);
        }

        $user = auth()->user();
        return response()->json([
            "jwt" => $this->respondWithToken($token),
            "user" => [
                "name" => $user->name,
                "email" => $user->email,
                "role" => $user->role->role
            ]
        ], 200);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout(true);

        return response()->json([
            'status' => true,
            'pesan' => 'Berhasil Logout Terimakasih',
        ], 200);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}

<?php

namespace App\Http\Controllers;


use App\Models\Artikel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArtikelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $artikels = Artikel::with(['user', 'tags', 'komentars', 'kategori'])->get();

        if($artikels->isEmpty()){
        
        return response()->json([
            'success' => false,
            'pesan' => 'Data Artikel Kosong',
        ], 404);
        
        } else {
        return response()->json([
            'success' => true,
            'data' => $artikels,
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
            'judul_artikel' => 'required|min:5|max:100',
            'isi' => 'required|min:100',
            'kategori_id' => 'required|exists:kategoris,id', 
            'user_id' => 'required|exists:users,id',
            'tags' => 'required|array|min:1',
            'tags.*' => 'exists:tags,id',
            'banner' => [
                'required',
                'regex:/^data:image\/(png|jpeg|jpg|gif|webp);base64,([A-Za-z0-9+\/=]+)$/'
            ]
        ],[
            // masukkan pesan error kamu di sini
            'judul_artikel.required' => 'Kolom Judul Artikel Harus Diisi',
            'judul_artikel.min' => 'Kolom Judul Artikel Minimal 5 kata',
            'judul_artikel.max' => 'Kolom Judul Artikel Maksimal 100',
            'isi.required' => 'Isi Artikel Harus Diisi',
            'isi.min' => 'Isi Artikel Minimal 100 Kata',
            'kategori_id.required' => 'Id Kategori Harus Diisi',
            'kategori_id.exists' => 'Data Kategori Yang Bersangkutan Tidak Ada',
            'user_id.required' => 'Id User Harus Diisi',
            'user_id.exists' => 'Data User Yang Bersangkutan Tidak Ada',
            'tags.required' => 'Tag Diisi minimal 1',
            'tags.array' => 'Tag Harus Berupa Array',
            'tags.min' => 'Tag Diisi minimal 1',
            'tags.*.exists' => 'Data Tag Yang Tidak Ada Di Data Kami',
            'banner.required' => 'Foto Artikel Harus Diisi',
            'banner.regex' => 'Foto Harus Berbentuk PNG, JPEG, JPG, GIF, WEBP'
        ]);

        // Simpan Gambar
        // Cek gambar ada atau tidak
        if($request->has('banner')) {
            // Ambil Base64 Siman Sebagai Variabel
            $imageData = $validate['banner'];

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

            // Save Image Ke Aplikasi public/image/artikel
            $saveFile = $this->uploudImage($imageData, $extension, 'image/artikel/');
            $validate['banner'] = $saveFile;
        } else {
            return response()->json([
                'success' => false,
                'pesan' => 'Banner Wajib Diisi',
                ], 422);
        }

        // jika berhasil masukkan datanya ke database
        $artikelBuat = Artikel::create($validate);

        // masukkan tags ke database 
        $artikelBuat->tags()->attach($validate['tags']);

        if($artikelBuat) {
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
        $artikel = Artikel::with(['user', 'tags', 'komentars', 'kategori'])->find($id);

        if(is_null($artikel)){    
            return response()->json([
                'success' => true,
                'data' => 'Data Artikel Kosong',
            ], 404);
            
        } else {
            return response()->json([
                'success' => true,
                'data' => $artikel,
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
        $artikel = Artikel::find($id);

        if(is_null($artikel)){    
            return response()->json([
                'success' => true,
                'data' => 'Data Artikel Kosong',
            ], 404);
            
        }

        // Ambil hanya field yang ada di tabel artikel
        $validFields = array_intersect_key($request->all(), $artikel->getAttributes()); 

        // Jika tidak ada field yang cocok 
        if (empty($validFields)) { 
            // kirimkan pesan gagal 
            return response()->json([
                'success' => false,
                'pesan' => 'Tidak Ada Kolom Yang Cocok',           
            ], 400);
        }

        $validate = $request->validate([
            'judul_artikel' => 'required|min:5|max:100',
            'isi' => 'required|min:100',
            'kategori_id' => 'required|exists:kategoris,id', 
            'user_id' => 'required|exists:users,id',
            'tags' => 'required|array|min:1',
            'tags.*' => 'exists:tags,id',
            'banner' => [
                'required',
                'regex:/^data:image\/(png|jpeg|jpg|gif|webp);base64,([A-Za-z0-9+\/=]+)$/'
            ]
        ],[
            // masukkan pesan error kamu di sini
            'judul_artikel.required' => 'Kolom Judul Artikel Harus Diisi',
            'judul_artikel.min' => 'Kolom Judul Artikel Minimal 5 kata',
            'judul_artikel.max' => 'Kolom Judul Artikel Maksimal 100',
            'isi.required' => 'Isi Artikel Harus Diisi',
            'isi.min' => 'Isi Artikel Minimal 100 Kata',
            'kategori_id.required' => 'Id Kategori Harus Diisi',
            'kategori_id.exists' => 'Data Kategori Yang Bersangkutan Tidak Ada',
            'user_id.required' => 'Id User Harus Diisi',
            'user_id.exists' => 'Data User Yang Bersangkutan Tidak Ada',
            'tags.required' => 'Tag Diisi minimal 1',
            'tags.array' => 'Tag Harus Berupa Array',
            'tags.min' => 'Tag Diisi minimal 1',
            'tags.*.exists' => 'Data Tag Yang Tidak Ada Di Data Kami',
            'banner.required' => 'Foto Artikel Harus Diisi',
            'banner.regex' => 'Foto Harus Berbentuk PNG, JPEG, JPG, GIF, WEBP'
        ]);

        // Simpan Gambar
        // Cek gambar ada atau tidak
        if($request->has('banner')) {
            // Ambil Base64 Siman Sebagai Variabel
            $imageData = $validate['banner'];

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

            // Save Image Ke Aplikasi public/image/artikel
            // Dan hapus image lamanya
            $saveFile = $this->uploudImage($imageData, $extension, 'image/artikel/', $artikel->banner);
            
            // Cekk Error Uploud Image
            if(is_array($saveFile)) {
                if(!$saveFile['success']) {
                    return response()->json([
                        'success' => false,
                        'pesan' => 'Uploud Image Error',
                        ], 500);
                }
            }

            $validate['banner'] = $saveFile;
        } else {
            return response()->json([
                'success' => false,
                'pesan' => 'Banner Wajib Diisi',
                ], 422);
        }

        // jika berhasil masukkan datanya ke database
        $artikelEdit = $artikel->update($validate);

        // edit juga tags nya yang ada di database tags_artikel 
        $artikel->tags()->sync($validate['tags']);

        if($artikelEdit) {
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
        $artikel = Artikel::find($id);

        if(is_null($artikel)){    
            return response()->json([
                'success' => true,
                'data' => 'Data Artikel Kosong',
            ], 404);
            
        }

        $artikelBannerDelete = $this->deleteImage($artikel->banner);
        if(is_array($artikelBannerDelete)) {
            if(!$artikelBannerDelete['success']) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Hapus Image Error',
                    ], 500);
            }
        }

        // hapus tags_artikel berdasarkan artikel yang dihapus
        $artikel->tags()->detach();

        // Hapus Artikel
        $deleteArtikel = $artikel->delete();

        if($deleteArtikel) {
            // Kembalikan response sukses
            return response()->json([
                'success' => true,
                'pesan' => 'Artikel Berhasil Dihapus',
            ], 200);
        } else {
            // Jika Artikel tidak ditemukan
            return response()->json([
                'success' => false, 
                'pesan' => 'Artikel gagal Dihapus',
            ], 400);
        }
    }
}

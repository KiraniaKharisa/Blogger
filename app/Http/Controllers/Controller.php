<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

abstract class Controller
{
    public function deleteImage($fileOld) {
        $fileOld = public_path($fileOld);;
        if($fileOld && File::exists($fileOld)) {
            File::delete($fileOld);
        } else {
            return false;
        }
    }
    public function uploudImage($imageData, $extension, $pathSave, $oldImage = null) {
        if($oldImage != null) {
            if($this->deleteImage($oldImage)) {
                return response()->json([
                    'success' => false,
                    'pesan' => 'Image Lama Tidak Ada',
                ],422);
            }
        }

        $folderPosisi = public_path($pathSave);

        // Cek Apakah Posisinya Ada Atau Tidak Jika Tidak Buatkan
        if(!File::exists($folderPosisi)) {
            File::makeDirectory($folderPosisi, 0775, true);
        }
        
        $fileName = $pathSave. uniqid() . '.' . $extension;
        file_put_contents(public_path($fileName), $imageData); // Save Image

        return $fileName; // Return Namanya Untuk Disimpan Ke Database
    }
}

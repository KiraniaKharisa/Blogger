<?php

namespace App\Models;

use App\Models\Artikel;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $guarded = [
        'id'
    ];

    // One To Many dengan Artikel
    public function artikels() {
        return $this->hasMany(Artikel::class);
    }
}

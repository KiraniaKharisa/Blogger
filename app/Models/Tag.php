<?php

namespace App\Models;

use App\Models\Artikel;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $guarded = [
        'id'
    ];

    // Many To Many Dengan Tags
    public function artikels() {
        return $this->belongsToMany(Artikel::class, 'tag_artikels');
    }
}

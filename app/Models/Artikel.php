<?php

namespace App\Models;

use App\Models\Tag;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Komentar;
use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    protected $guarded = [
        'id'
    ];

    // Many To Many Dengan Tags
    public function tags() {
        return $this->belongsToMany(Tag::class, 'tag_artikels');
    }

    // One To Many dengan Komentar
    public function komentars() {
        return $this->hasMany(Komentar::class);
    }

    // One To Many dengan Kategori
    public function kategori() {
        return $this->belongsTo(Kategori::class);
    }

    // One To Many dengan User
    public function user() {
        return $this->belongsTo(User::class);
    }
}

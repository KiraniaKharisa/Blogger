<?php

namespace App\Models;

use App\Models\User;
use App\Models\Artikel;
use Illuminate\Database\Eloquent\Model;

class Komentar extends Model
{
    protected $guarded = [
        'id'
    ];

    // One to Many dengan Artikel
    public function artikel() {
        return $this->belongsTo(Artikel::class);
    }

    // One to Many dengan User
    public function user() {
        return $this->belongsTo(User::class);
    }
}

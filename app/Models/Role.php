<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $guarded = [
        'id'
    ];

    // One to Many dengan User 
    public function users() {
        return $this->hasMany(User::class);
    }
}

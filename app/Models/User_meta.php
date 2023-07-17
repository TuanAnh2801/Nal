<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_meta extends Model
{
    use HasFactory;

    protected $table = 'user_metas';
    protected $fillable = [
        'token',
        'avatar',
        'user_id'
    ];
}

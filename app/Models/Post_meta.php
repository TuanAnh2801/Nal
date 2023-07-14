<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post_meta extends Model
{
    use HasFactory;
    protected $table= 'post_meta';
    protected $fillable =[
        'post_id',
        'key',
        'value'
    ];
}

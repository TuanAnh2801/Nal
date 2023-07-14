<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $fillable = [
        'name',
        'slug',
        'status',
        'description',
        'type',
        'user_id'
    ];

    public function post()
    {
        return $this->belongsToMany(Post::class, 'post_categories', 'category_id', 'post_id');
    }
}

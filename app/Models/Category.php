<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['name', 'description', 'status', 'slug', 'type', 'image', 'author', 'created_at', 'updated_at', 'deleted_at'];
    public function post()
    {
        return $this->belongsToMany(Post::class);
    }
    public function article()
    {
        return $this->belongsToMany(Article::class);
    }
    public function image()
    {
        return $this->hasOne(Upload::class);
    }
}

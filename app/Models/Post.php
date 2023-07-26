<?php

namespace App\Models;

use App\Models\UserMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;
class Post extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['title', 'content', 'status', 'slug', 'type', 'author', 'created_at', 'updated_at', 'deleted_at'];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
    public function post_meta()
    {
        return $this->hasMany(PostMeta::class);
    }
    public function post_detail(){
        return $this->hasMany(PostDetail::class);
    }
    public function image()
    {
        return $this->morphMany(Upload::class, 'id_item');
    }
}

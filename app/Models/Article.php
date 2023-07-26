<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'title', 'content', 'thumbnail', 'status', 'category_id', 'author '];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
    public function article_detail(){
        return $this->hasMany(ArticleDetail::class);
    }
}

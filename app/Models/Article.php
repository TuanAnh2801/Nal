<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['slug', 'title', 'content', 'thumbnail', 'status', 'category_id', 'author '];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
    public function article_detail(){
        return $this->hasMany(ArticleDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function revision()
    {
        return $this->hasMany(Revision::class);
    }
}

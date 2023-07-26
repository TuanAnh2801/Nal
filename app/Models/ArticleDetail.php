<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleDetail extends Model
{
    use HasFactory;
    protected $fillable = ['article_id ','title','content','lang'];
    public function post(){
        return $this->belongsTo(Article::class);
    }
}

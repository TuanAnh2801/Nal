<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Revision extends Model
{
    use HasFactory;
    protected $fillable = [ 'title', 'content','description', 'article_id', 'version '];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
    public function revision_detail()
    {
        return $this->hasMany(RevisionDetail::class);
    }

}

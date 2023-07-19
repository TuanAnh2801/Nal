<?php

namespace App\Models;

use App\Traits\HasPermission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Permission extends Model
{
    use HasFactory,HasPermission ;
    protected $fillable = ['name'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}

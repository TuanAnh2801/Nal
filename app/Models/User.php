<?php

namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject,MustVerifyEmail
{
    use Notifiable;
    use SoftDeletes;
    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'password',
        'token',
        'avatar',
        'is_email_verified'
    ];
   public function categories(){
       return $this->hasMany(Category::class);
   }
//    public function user_meta()
//    {
//        return $this->has(Post_meta::class);
//    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}

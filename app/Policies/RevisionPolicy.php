<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use App\Traits\HasPermission;
use Illuminate\Auth\Access\HandlesAuthorization;

class RevisionPolicy
{
    use HandlesAuthorization, HasPermission;

    public function before(User $user)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function create(User $user)
    {
        return $user->hasRole('editor') || $user->hasRole('user')|| $user->hasPermission('create');
    }
    public function read(User $user, Article $article)
    {
        return $user->hasRole('editor') ||  $user->id === $article->user_id;
    }
    public function update(User $user, Article $article)
    {
        return $user->hasRole('editor') ||  $user->id === $article->user_id;
    }


}

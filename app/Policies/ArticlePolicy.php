<?php

namespace App\Policies;

use App\Models\User;
use App\Traits\HasPermission;
use Illuminate\Auth\Access\HandlesAuthorization;

class ArticlePolicy
{
    use HandlesAuthorization, HasPermission;

    public function before(User $user)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }
    public function show(User $user)
    {
        return $user->hasRole('editor') || $user->hasRole('user')|| $user->hasPermission('read');
    }
    public function create(User $user)
    {
        return $user->hasRole('editor') || $user->hasRole('user')|| $user->hasPermission('create');
    }

    public function update(User $user, Post $post)
    {
        return $user->hasRole('editor') || $user->hasPermission('update');
    }

    public function delete(User $user)
    {
        return $user->hasPermission('delete');
    }
    public function restore(User $user)
    {
        return $user->hasPermission('update');
    }
}

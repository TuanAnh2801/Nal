<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Post;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Traits\HasPermission;


class PostPolicy
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
        return $user->hasRole('editor') || $user->hasPermission('read');
    }
    public function create(User $user)
    {
        return $user->hasRole('editor') || $user->hasPermission('create');
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

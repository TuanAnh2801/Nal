<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Traits\HasPermission;


class UserPolicy
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
        return $user->hasPermission('read');
    }

    public function viewAll(User $user)
    {
        return $user->hasPermission('read');
    }

    public function create(User $user)
    {
        return $user->hasPermission('create');
    }
    public function updateAll(User $user)
    {
        return $user->hasPermission('update');
    }

    public function delete(User $user)
    {
        return $user->hasPermission('delete');
    }

}

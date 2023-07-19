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
    }

    public function viewAll(User $user)
    {
    }

    public function create(User $user)
    {
    }
    public function updateAll(User $user)
    {
    }

    public function delete(User $user)
    {
    }

}

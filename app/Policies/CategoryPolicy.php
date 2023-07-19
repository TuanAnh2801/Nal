<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Traits\HasPermission;

class CategoryPolicy
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
        return $user->hasRole('editor');
    }

    public function update(User $user)
    {
        return $user->hasRole('editor');
    }

    public function delete()
    {
    }

    public function restore()
    {
    }

}

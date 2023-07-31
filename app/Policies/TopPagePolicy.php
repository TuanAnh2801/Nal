<?php

namespace App\Policies;

use App\Models\TopPage;
use App\Models\User;
use App\Traits\HasPermission;
use Illuminate\Auth\Access\HandlesAuthorization;

class TopPagePolicy
{
    use HandlesAuthorization, HasPermission;

    public function before(User $user)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }
    public function show(User $user,TopPage $topPage)
    {
        return $user->hasRole('editor') || $user->id === $topPage->user_id;
    }
    public function create(User $user)
    {
        return $user->hasRole('editor') || $user->hasRole('user')|| $user->hasPermission('create');
    }

    public function update(User $user, TopPage $topPage)
    {
        return $user->hasRole('editor') ||  $user->id === $topPage->user_id;
    }

}

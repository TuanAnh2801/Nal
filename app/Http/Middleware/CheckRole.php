<?php

namespace App\Http\Middleware;

use App\Http\Controllers\BaseController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole extends BaseController
{

    public function handle(Request $request, Closure $next , $roles)
    {
        if (auth()->check()) {
            $userRoles = auth()->user()->roles->pluck('name')->toArray();
            foreach ($roles as $role) {
                if (in_array($role, $userRoles)) {
                    return $next($request);
                }
            }
        }

         return $this->handleRespondError('User does not have permission');
    }
}

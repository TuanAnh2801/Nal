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
        if (Auth::check() && Auth::user()->role == $roles ) {
            return $next($request);
        }

         return $this->handleRespondError('User does not have permission');
    }
}

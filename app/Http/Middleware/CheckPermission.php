<?php

namespace App\Http\Middleware;

use App\Http\Controllers\BaseController;
use App\Models\Permission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckPermission extends BaseController
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission = null)
    {
        $list_roles = DB::table('users')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('users.id', Auth::id())
            ->select('roles.*')
            ->get()->pluck('id')->toArray();

        $list_permission = DB::table('roles')
            ->join('role_permission', 'roles.id', '=', 'role_permission.role_id')
            ->join('permissions', 'permissions.id', '=', 'role_permission.permission_id')
            ->whereIn('roles.id', $list_roles)
            ->select('permissions.*')
            ->get()->pluck('id')->unique();
        $checkPermission = Permission::where('name', $permission)->value('id');
        if ($list_permission->contains($checkPermission)) {
            return $next($request);
        }
        return $this->handleRespondError('Ban k co quyen');
    }
}

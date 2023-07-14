<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            // Xử lý nếu token không hợp lệ, hoặc không tìm thấy người dùng
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Lưu thông tin người dùng vào request để có thể sử dụng trong controller
        $request->user = $user;

        return $next($request);
    }
}

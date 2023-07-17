<?php

namespace App\Http\Middleware;

use App\Http\Controllers\BaseController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JWTMiddleware extends BaseController
{

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            // Xử lý nếu token không hợp lệ, hoặc không tìm thấy người dùng
            return $this->handleRespondError('please login!');
        }

        // Lưu thông tin người dùng vào request để có thể sử dụng trong controller
        $request->user = $user;

        return $next($request);
    }
}

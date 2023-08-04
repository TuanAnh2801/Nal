<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Requests\DeleteRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Traits\HasPermission;

class AuthController extends BaseController
{
    use HasPermission;


    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return $this->handleRespondError('Tai khoan hoac mat khau k dung');
        }
        return $this->respondWithToken($token);
    }

    public function register(AuthRequest $request)
    {
        $role_id = $request->roles;
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        $user->roles()->sync($role_id);
        event(new Registered($user));
        return $this->handleRespondSuccess('register success', $user);
    }


    public function update(AuthRequest $request)
    {
        $user = Auth::user();
        $path = 'public' . Str::after($user->avatar, 'storage');
        Storage::delete($path);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        return $this->handleRespondSuccess('update success', $user);
    }

    public function destroy(DeleteRequest $request)
    {
        $user_delete = $request->input('ids');
        $option = $request->option;
        $users = User::withTrashed()->whereIn('id', $user_delete)->get();
        if ($users) {
            foreach ($users as $user) {
                $user->save();
                if ($option === 'delete') {
                    $user->delete();
                } elseif ($option === 'forceDelete') {
                    $user->forceDelete();
                }

            }
            return $this->handleRespondSuccess('delete success', []);
        }
        return $this->handleRespondError('delete false');
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }


    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }


    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 120
        ]);
    }
}

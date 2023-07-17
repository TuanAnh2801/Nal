<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AuthController extends BaseController
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */

    public function show()
    {
        $user = User::all();
        return $this->handleRespondSuccess('user', $user);
    }

    public function me()
    {
        $user = Auth::user();
        return $this->handleRespondSuccess('user', $user);
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
    }

    public function register(Request $request)
    {
        $user = new User();
        $image = $request->image;
        if ($image) {
            $image_name = Str::random(10);
            $image_path = $image->storeAs('public/userImage/' . date('Y/m/d'), $image_name);
            $image_url = asset(Storage::url($image_path));
            $user->avatar = $image_url;
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        event(new Registered($user));
        return $this->handleRespondSuccess('register success', $user);
    }

    public function updateAll(Request $request, User $user)
    {
        $image = $request->image;
        if (!$request->hasFile('image')) {
            $user->update($request->all());
            return $this->handleRespondSuccess('update success', $user);
        }
        $image_name = Str::random(10);
        $path = 'public' . Str::after($user->avatar, 'storage');
        Storage::delete($path);
        $image_path = $image->storeAs('public/userImage/' . date('Y/m/d'), $image_name);
        $image_url = asset(Storage::url($image_path));
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->avatar = $image_url;
        $user->save();
        return $this->handleRespondSuccess('update success', $user);
    }

    public function updateMe(Request $request)
    {
        $user = Auth::user();
        if (!$request->hasFile('image')) {
            $user->update($request->all());
            return $this->handleRespondSuccess('update success', $user);
        }
        $image = $request->image;
        $image_name = Str::random(10);
        $path = 'public' . Str::after($user->avatar, 'storage');
        Storage::delete($path);
        $image_path = $image->storeAs('public/userImage/' . date('Y/m/d'), $image_name);
        $image_url = asset(Storage::url($image_path));
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->avatar = $image_url;
        $user->save();
        return $this->handleRespondSuccess('update success', $user);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'ids' => 'required',
            'option' => 'required|in:delete,forceDelete'
        ]);
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
                    $avatar_url = $user->avatar;
                    $path = 'public' . Str::after($avatar_url, 'storage');
                    Storage::delete($path);
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

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Traits\HasPermission;

class UserController extends BaseController
{
    use HasPermission;

    public function index(Request $request)
    {
        if (Auth::user()->hasPermission('read')) {
            $status = $request->input('status');
            $layout_status = ['active', 'deactivate'];
            $sort = $request->input('sort');
            $sort_types = ['desc', 'asc'];
            $sort_option = ['title', 'created_at', 'updated_at'];
            $sort_by = $request->input('sort_by');
            $status = in_array($status, $layout_status) ? $status : 'active';
            $sort = in_array($sort, $sort_types) ? $sort : 'desc';
            $sort_by = in_array($sort_by, $sort_option) ? $sort_by : 'created_at';
            $search = $request->input('query');
            $limit = request()->input('limit') ?? config('app.paginate');
            $query = User::select('*');
            if ($status) {
                $query = $query->where('status', $status);
            }
            if ($search) {
                $query = $query->where('title', 'LIKE', '%' . $search . '%');
            }
            $users = $query->orderBy($sort_by, $sort)->paginate($limit);

            return $this->handleRespondSuccess('Get posts successfully', $users);
        }
        return $this->handleRespondError('you do not have access');
    }

    public function show()
    {
        if (Auth::user()->hasPermission('read')) {
            $data = User::all();
            return $this->handleRespondSuccess('data', $data);
        }
        return $this->handleRespondError('you do not have access');
    }

    public function view()
    {
        $user = Auth::user();
        return $this->handleRespondSuccess('user', $user);
    }

    public function create(Request $request)
    {
        if (Auth::user()->hasPermission('create')) {
            $request->validate([
                'name' => 'required|string|between:2,100',
                'email' => 'required|string|email|max:100',
                'password' => 'required|string',
                'roles' => 'required|array'
            ]);
            $role_id = $request->roles;
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
            $user->roles()->sync($role_id);
            event(new Registered($user));
            return $this->handleRespondSuccess('register success', $user);
        }
    }

    public function updateAll(Request $request, User $user)
    {
        if (Auth::user()->hasPermission('update')) {
            $request->validate([
                'name' => 'required|string|between:2,100',
                'email' => 'required|string|email|max:100',
                'password' => 'required|string',
                'roles' => 'required|array'
            ]);
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
        return $this->handleRespondError('you do not have access');
    }


    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100',
            'password' => 'required|string',
            'roles' => 'required|array'
        ]);
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
        $user = Auth::user();
        if ($user->hasPermission('delete')) {
//            $request->validate([
//                'option' => 'required|in:delete,forceDelete'
//            ]);
            $option = $request->option;
            if ($option === 'delete') {
                $user->delete();
            } elseif ($option === 'forceDelete') {
                $user->forceDelete();
                $avatar_url = $user->avatar;
                $path = 'public' . Str::after($avatar_url, 'storage');
                Storage::delete($path);
            }
            return $this->handleRespondSuccess('delete success', []);
        }
        return $this->handleRespondError('you do not have access');
    }

}


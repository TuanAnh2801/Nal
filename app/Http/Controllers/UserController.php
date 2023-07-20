<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Models\UserMeta;
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
        if (!Auth::user()->hasPermission('read')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
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

    public function show()
    {
        if (!Auth::user()->hasPermission('read')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $data = User::all();
        return $this->handleRespondSuccess('data', $data);

    }

    public function view()
    {
        $user = Auth::user();
        return $this->handleRespondSuccess('user', $user);
    }

    public function create(Request $request)
    {
        if (!Auth::user()->hasPermission('create')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
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

    public function updateAll(Request $request, User $user)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
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
        if (!Auth::user()->hasPermission('delete')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }

        $request->validate([
            'ids' => 'required',
            'option' => 'required|in:delete,forceDelete'
        ]);
        $user_delete = $request->input('ids');
        $option = $request->option;
        $users = User::withTrashed()->whereIn('id', $user_delete)->get();
        if ($users) {
            foreach ($users as $user) {
                if ($option === 'delete') {
                    $user->status = 'deactivate';
                    $user->save();
                    $user->delete();
                } elseif ($option === 'forceDelete') {
                    $user->forceDelete();
                }
            }
        }
        return $this->handleRespondSuccess('delete success', []);
    }

    public function setMood(Request $request)
    {
        $request->validate([
            'key' => 'required',
            'value' => 'required',
        ]);
        if ($request->has('key') && $request->has('value')) {
            $user_meta = new UserMeta();
            $user_meta->user_id = Auth::id();
            $user_meta->key = $request->key;
            $user_meta->value = $request->value;
            $user_meta->save();
            return $this->handleRespondSuccess('setMood success ', $user_meta);
        }
        return $this->handleRespondError('Please enter key and value');
    }

    public function updateMood(Request $request, UserMeta $userMeta)
    {
        $request->validate([
            'key' => 'required',
            'value' => 'required',
        ]);
        if ($userMeta) {
            $userMeta->key = $request->key;
            $userMeta->value = $request->value;
            $userMeta->save();
            return $this->handleRespondSuccess('update success', $userMeta);
        }
        return $this->handleRespondError('update false');
    }

    public function getMood()
    {
        $post_id = Auth::user()->user_meta()->where('key', 'favorite')->pluck('value');
        $post = Post::find($post_id);
        return $this->handleRespondSuccess('get success', $post);
    }


}


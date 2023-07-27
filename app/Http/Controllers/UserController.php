<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Upload;
use App\Models\User;
use App\Models\UserMeta;
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
        $sort = $request->input('sort');
        $sort_types = ['desc', 'asc'];
        $sort_option = ['title', 'created_at', 'updated_at'];
        $sort_by = $request->input('sort_by');
        $sort = in_array($sort, $sort_types) ? $sort : 'desc';
        $sort_by = in_array($sort_by, $sort_option) ? $sort_by : 'created_at';
        $search = $request->input('query');
        $limit = request()->input('limit') ?? config('app.paginate');
        $query = User::select('*');
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
        $id_uploads = $request->uploadId;
        $role_id = $request->roles;
        $user = new User();
        $url = $request->url;
        $user->avatar = $url;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        if ($id_uploads) {
            $upload = Upload::find($id_uploads);
            $upload->user_id = $user->id;
            $upload->status = 'published';
            $upload->save();
        }
        $upload_deletes = Upload::where('status', 'pending')->where('author', Auth::id())->get();
        foreach ($upload_deletes as $upload_delete) {
            $thumbnail = $upload_delete->thumbnail;
            $path = 'public' . Str::after($thumbnail, 'storage');
            Storage::delete($path);
        }
        Upload::where('status', 'pending')->where('author', Auth::id())->delete();
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
        $id_uploads = $request->uploadId;
        if ($id_uploads){
            $uploads = $user->image();
            foreach ($uploads as $upload){
                $upload->delete();
            }
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        if ($id_uploads) {
            $upload = Upload::find($id_uploads);
            $upload->user_id = $user->id;
            $upload->status = 'published';
            $upload->save();
        }
        $upload_deletes = Upload::where('status', 'pending')->where('author', Auth::id())->get();
        foreach ($upload_deletes as $upload_delete) {
            $thumbnail = $upload_delete->thumbnail;
            $path = 'public' . Str::after($thumbnail, 'storage');
            Storage::delete($path);
        }
        Upload::where('status', 'pending')->where('author', Auth::id())->delete();
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
        $id_uploads = $request->uploadId;
        $user = Auth::user();
        if ($id_uploads){
            $uploads = $user->image();
            foreach ($uploads as $upload){
                $upload->delete();
            }
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        if ($id_uploads) {
            $upload = Upload::find($id_uploads);
            $upload->user_id = $user->id;
            $upload->status = 'published';
            $upload->save();
        }
        Upload::where('status', 'pending')->where('author', Auth::id())->delete();
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
                    $upload = $user->image();
                    $thumbnail = $upload->thumbnail;
                    $path = 'public' . Str::after($thumbnail, 'storage');
                    Storage::delete($path);
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
            'value' => 'required|array',
        ]);
        $key = $request->key;
        $value = $request->value;
        $user_metas = $request->user()->user_meta();
        if ($key === 'favorite') {
            if ($user_metas->where('key', 'favorite')->exists()) {
                $user_meta = $user_metas->where('key', 'favorite')->first();
                $favorite = explode(',', $user_meta->value);
                $favorite = array_unique(array_merge($favorite, $value));
                $user_meta->value = implode(',', $favorite);
                $user_meta->save();
                return $this->handleRespondSuccess('setmood success', $user_meta);
            }
            $user_meta = new UserMeta();
            $user_meta->user_id = Auth::id();
            $user_meta->key = $request->key;
            $user_meta->value = implode(',', $value);
            $user_meta->save();
            return $this->handleRespondSuccess('setmood success', $user_meta);
        }
        if ($key === 'unfavorite') {
            $user_meta = $user_metas->where('key', 'favorite')->first();
            $unfavorite = $request->value;
            $favorite = explode(',', $user_meta->value);
            $update_favorite = array_diff($favorite, $unfavorite);
            if ($update_favorite === []) {
                $user_meta->delete();
                return $this->handleRespondSuccess('unfavorite success', true);
            }
            $user_meta->value = implode(',', $update_favorite);
            $user_meta->save();
            return $this->handleRespondSuccess('unfavorite success', true);
        }


    }

    public function getMood()
    {
        $post_id = Auth::user()->user_meta()->where('key', 'favorite')->pluck('value');
        $post = Post::find($post_id);
        return $this->handleRespondSuccess('get success', $post);
    }


}


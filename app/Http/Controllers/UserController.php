<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveArticleRequest;
use App\Http\Requests\DeleteRequest;
use App\Http\Requests\UserRequest;
use App\Mail\Mailback;
use App\Models\Article;
use App\Models\Post;
use App\Models\Revision;
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
use Illuminate\Support\Facades\Mail;

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
    public function create(UserRequest $request)
    {
        if (!Auth::user()->hasPermission('create')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $id_uploads = $request->uploadId;
        $id_upload = implode(',', $id_uploads);
        $role_id = $request->roles;
        $user = new User();
        $url = $request->url;
        $user->avatar = $url;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->upload_id = $id_upload;
        $user->password = Hash::make($request->password);
        $user->save();
        if ($id_uploads) {
            handleUpload($id_uploads);
        }

        $user->roles()->sync($role_id);
        event(new Registered($user));
        return $this->handleRespondSuccess('create user success', $user);
    }

    public function view()
    {
        $user = Auth::user();
        $uploads = $user->upload_id;
        $uploads = explode(',', $uploads);
        if ($uploads) {
            foreach ($uploads as $upload) {
                $image[] = Upload::where('id', $upload)->pluck('url')->first();
            }
            $user->image = $image;
        }
        return $this->handleRespondSuccess('user data', $user);
    }

    public function show()
    {
        if (!Auth::user()->hasPermission('read')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $users = User::all();
        foreach ($users as $user) {
            $uploads = $user->upload_id;
            $uploads = explode(',', $uploads);
            if ($uploads) {
                foreach ($uploads as $upload) {
                    $image[] = Upload::where('id', $upload)->pluck('url')->first();
                }
                $user->image = $image;
            }
        }
        return $this->handleRespondSuccess('data', $users);

    }

    public function updateAll(UserRequest $request, User $user)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $id_uploads = $request->uploadId;
        $removal_folder = $request->removalFolder;
        if ($id_uploads) {
            $upload_id = $user->upload_id;
            $upload_id = explode(',', $upload_id);
            $folder_is_kept = array_diff($upload_id, $removal_folder);
            $upload_deletes = Upload::whereIn('id', $removal_folder)->get();
            Upload::whereIn('id', $removal_folder)->delete();
            foreach ($upload_deletes as $upload_delete) {
                $url = $upload_delete->url;
                $path = 'public' . Str::after($url, 'storage');
                Storage::delete($path);
            }
            handleUpload($id_uploads);
            $id_uploadNew = array_merge($folder_is_kept, $id_uploads);
            $id_uploadNew = implode(',', $id_uploadNew);
            $user->upload_id = $id_uploadNew;
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        return $this->handleRespondSuccess('update user success', $user);
    }

    public function update(UserRequest $request)
    {
        $user = Auth::user();
        $id_uploads = $request->uploadId;
        $removal_folder = $request->removalFolder;
        if ($id_uploads) {
            $upload_id = $user->upload_id;
            $upload_id = explode(',', $upload_id);
            $folder_is_kept = array_diff($upload_id, $removal_folder);
            $upload_deletes = Upload::whereIn('id', $removal_folder)->get();
            Upload::whereIn('id', $removal_folder)->delete();
            foreach ($upload_deletes as $upload_delete) {
                $url = $upload_delete->url;
                $path = 'public' . Str::after($url, 'storage');
                Storage::delete($path);
            }
            handleUpload($id_uploads);
            $id_uploadNew = array_merge($folder_is_kept, $id_uploads);
            $id_uploadNew = implode(',', $id_uploadNew);
            $user->upload_id = $id_uploadNew;
        }
            $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        return $this->handleRespondSuccess('update profile success', $user);
    }

    public function approveArticle(ApproveArticleRequest $request)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $status = $request->status;
        $reason = $request->reason;
        $id_article = $request->idArticle;
        $article = Article::find($id_article);
        $user = User::find($article->user_id);
        $email = $user->email;
        if ($status === 'published') {
            $article->status = $status;
            $article->save();
            Mail::to($email)->send(new Mailback($status, ''));
            return $this->handleRespondSuccess('update status article success', $article);
        } elseif ($status = 'reject') {
            Mail::to($email)->send(new Mailback($status, $reason));
            return $this->handleRespondSuccess('update status article success', $article);
        }
        return $this->handleRespondError('update status false');
    }

    public function approveRevision(Request $request, Revision $revision)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $reason = $request->reason;
        $status = $request->status;
        $article = $revision->article()->first();
        $user = User::find($article->user_id);
        $email = $user->email;
        if ($status === 'access') {
            $languages = config('app.languages');
            $upload_id_article = explode(',',$article->upload_id) ;
            $upload_id_revision = explode(',',$revision->upload_id) ;
            $upload_id_article_save = array_intersect($upload_id_revision, $upload_id_article);
            $upload_id_article_delete = array_diff($upload_id_article,$upload_id_article_save);
            $article->title = $revision->title;
            $article->description = $revision->description;
            $article->content = $revision->content;
            $article->upload_id = $revision->upload_id;
            $article->save();
            foreach ($languages as $language) {
                $article_detail = $article->article_detail()->where('lang', $language)->first();
                $revision_detail = $revision->revision_detail()->where('lang', $language)->first();
                $article_detail->title = $revision_detail->title;
                $article_detail->content = $revision_detail->content;
                $article_detail->save();
            }
            $upload_deletes = Upload::whereIn('id', $upload_id_article_delete)->get();
            Upload::whereIn('id', $upload_id_article_delete)->delete();
            foreach ($upload_deletes as $upload_delete) {
                $url = $upload_delete->url;
                $path = 'public' . Str::after($url, 'storage');
                Storage::delete($path);
            }

            $revision_deletes = $article->revision()->where('version', '!=', $revision->version)->get();
            $article->revision()->delete();
            foreach ($revision_deletes as $revision_delete) {
                $upload_id = $revision_delete->upload_id;
                $upload_id = explode(',', $upload_id);
                $upload_deletes = Upload::whereIn('id', $upload_id)->get();
                Upload::whereIn('id', $upload_id)->delete();
                foreach ($upload_deletes as $upload_delete) {
                    $url = $upload_delete->url;
                    $path = 'public' . Str::after($url, 'storage');
                    Storage::delete($path);
                }
            }
            Mail::to($email)->send(new Mailback($status, ''));
            return $this->handleRespondSuccess('fix article success', $article);
        } elseif ($status === 'reject') {
            Mail::to($email)->send(new Mailback($status, $reason));
            return $this->handleRespondSuccess('rejected the edit success', $article);
        }
        return $this->handleRespondError('update status false');

    }


    public function destroy(DeleteRequest $request)
    {
        if (!Auth::user()->hasPermission('delete')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
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
                    $upload_id = $user->upload_id;
                    $upload_id = explode(',', $upload_id);
                    $upload_deletes = Upload::whereIn('id', $upload_id)->get();
                    Upload::whereIn('id', $upload_id)->delete();
                    foreach ($upload_deletes as $upload_delete) {
                        $url = $upload_delete->url;
                        $path = 'public' . Str::after($url, 'storage');
                        Storage::delete($path);
                        $user->forceDelete();
                    }
                }
            }
            return $this->handleRespondSuccess('delete user success', []);
        }
    }

    public function setMood(Request $request)
    {
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


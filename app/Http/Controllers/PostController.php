<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\PostRequest;
use App\Models\PostDetail;
use App\Models\PostMeta;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\HasPermission;

class PostController extends BaseController
{
    use HasPermission;

    public function index(Request $request)
    {

        $language = $request->input('language');
        $languages = config('app.languages');
        $language = in_array($language, $languages) ? $language : '';
        $status = $request->input('status');
        $layout_status = ['draft', 'published', 'archived'];
        $sort = $request->input('sort');
        $sort_types = ['desc', 'asc'];
        $sort_option = ['title', 'created_at', 'updated_at'];
        $sort_by = $request->input('sort_by');
        $status = in_array($status, $layout_status) ? $status : 'draft';
        $sort = in_array($sort, $sort_types) ? $sort : 'desc';
        $sort_by = in_array($sort_by, $sort_option) ? $sort_by : 'created_at';
        $search = $request->input('query');
        $limit = request()->input('limit') ?? config('app.paginate');
        $query = Post::select('*');
        if ($status) {
            $query = $query->where('status', $status);
        }
        if ($search) {
            $query = $query->where('title', 'LIKE', '%' . $search . '%');
        }
        if ($language) {
            $query = $query->whereHas('post_detail', function ($q) use ($language) {
                $q->where('lang', $language);
            });
            $query = $query->with(['post_detail' => function ($q) use ($language) {
                $q->where('lang', $language);
            }]);

        }
        $posts = $query->orderBy($sort_by, $sort)->paginate($limit);

        return $this->handleRespondSuccess('Get posts successfully', $posts);
    }

    public function store(PostRequest $request, Post $post)
    {
        if (!Auth::user()->hasPermission('create')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $user = Auth::id();
        $id_uploads = $request->uploadId;
        $id_upload = implode(',', $id_uploads);
        $languages = config('app.languages');
        $title = $request->title;
        $status = $request->status;
        $content = $request->contents;
        $type = $request->type;
        $category_id = $request->category;
        $post->title = $title;
        $post->status = $status;
        $post->content = $content;
        $post->type = $type;
        $post->upload_id = $id_upload;
        $post->author = $user;
        $post->slug = Str::slug($title);
        $post->save();
        if ($id_uploads) {
            foreach ($id_uploads as $id_upload) {
                $upload = Upload::find($id_upload);
                $upload->status = 'active';
                $upload->save();
            }
        }
        $upload_deletes = Upload::where('status', 'pending')->where('author', Auth::id())->get();
        foreach ($upload_deletes as $upload_delete) {
            $thumbnail = $upload_delete->url;
            $path = 'public' . Str::after($thumbnail, 'storage');
            Storage::delete($path);
        }
        Upload::where('status', 'pending')->where('author', Auth::id())->delete();
        $post->categories()->sync($category_id);
        foreach ($languages as $language) {
            $post_detail = new PostDetail();
            $post_detail->title = languages($language, $title);
            $post_detail->content = languages($language, $content);
            $post_detail->lang = $language;
            $post_detail->post_id = $post->id;
            $post_detail->save();
        }
        if ($request->has('key') && $request->has('value')) {
            $key = $request->key;
            $value = $request->value;
            foreach ($key as $i => $meta_key) {
                $post_meta = new PostMeta();
                $post_meta->post_id = $post->id;
                $post_meta->key = $meta_key[$i];
                $post_meta->value = $value[$i];
                $post_meta->save();
            }
        }
        $meta_data = $post->post_meta()->get();
        return $this->handleRespondSuccess('create success', [
            'post' => $post,
            'post_meta' => $meta_data
        ]);
    }

    public function show(Post $post, Request $request)
    {
        $language = $request->language;
        $category = $post->categories()->where('status', '=', 'active')->get();
        $post_detail = $post->post_detail()->where('lang', '=', $language)->get();
        $data = [
            'category' => $category,
            'post' => $post,
            'post_detail' => $post_detail
        ];
        return $this->handleRespondSuccess('data', $data);
    }

    public function update(PostRequest $request, Post $post)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $id_uploads = $request->uploadId;
        if ($id_uploads) {
            $id_uploadNew = implode(',', $id_uploads);
            $upload_id = $post->upload_id;
            $upload_id = explode(',', $upload_id);
            $upload_deletes = Upload::whereIn('id', $upload_id)->get();
            Upload::whereIn('id', $upload_id)->delete();
            foreach ($upload_deletes as $upload_delete) {
                $url = $upload_delete->url;
                $path = 'public' . Str::after($url, 'storage');
                Storage::delete($path);
            }
            foreach ($id_uploads as $id_upload) {
                $upload = Upload::find($id_upload);
                $upload->status = 'active';
                $upload->save();
            }
            $upload_useless = Upload::where('status', 'pending')->where('author', Auth::id())->get();
            foreach ($upload_useless as $upload_useles) {
                $thumbnail = $upload_useles->url;
                $path = 'public' . Str::after($thumbnail, 'storage');
                Storage::delete($path);
            }
            Upload::where('status', 'pending')->where('author', Auth::id())->delete();
            $post->upload_id = $id_uploadNew;
        }
        $languages = config('app.languages');
        $title = $request->title;
        $status = $request->status;
        $content = $request->contents;
        $type = $request->type;
        $category_id = $request->category;
        $post->title = $title;
        $post->status = $status;
        $post->content = $content;
        $post->type = $type;
        $post->slug = Str::slug($title);
        $post->save();
        $post->categories()->sync($category_id);
        $post->post_detail()->delete();
        foreach ($languages as $language) {
            $post_detail = new PostDetail();
            $post_detail->title = languages($language, $title);
            $post_detail->content = languages($language, $content);
            $post_detail->lang = $language;
            $post_detail->post_id = $post->id;
            $post_detail->save();
        }
        if ($request->has('key') && $request->has('value')) {
            $key = $request->key;
            $value = $request->value;
            foreach ($key as $i => $meta_key) {
                $post_meta = new PostMeta();
                $post_meta->post_id = $post->id;
                $post_meta->key = $meta_key[$i];
                $post_meta->value = $value[$i];
                $post_meta->save();
            }
            $meta_data = $post->post_meta()->get();
            return $this->handleRespondSuccess('update success', [
                'post' => $post,
                'post_meta' => $meta_data
            ]);
        }
    }

    public function update_postDetail(Request $request, Post $post)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $language = $request->language;
        $post_detail = $post->post_detail()->where('lang', $language)->first();
        if ($post_detail !== null) {
            $post_detail->title = $request->title;
            $post_detail->content = $request->contents;
            $post_detail->save();
            return $this->handleRespondSuccess('update post_detail success', $post_detail);
        }
        return $this->handleRespondError('update post_detail false');

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
        $post_delete = $request->input('ids');
        $option = $request->option;
        $posts = Post::withTrashed()->whereIn('id', $post_delete)->get();
        if ($posts) {
            foreach ($posts as $post) {
                $post->status = 'deactivate';
                $post->save();
                if ($option === 'delete') {
                    $post->delete();
                } elseif ($option === 'forceDelete') {
                    $upload_id = $post->upload_id;
                    $upload_id = explode(',', $upload_id);
                    $upload_deletes = Upload::whereIn('id', $upload_id)->get();
                    Upload::whereIn('id', $upload_id)->delete();
                    foreach ($upload_deletes as $upload_delete) {
                        $url = $upload_delete->url;
                        $path = 'public' . Str::after($url, 'storage');
                        Storage::delete($path);
                    }
                    $post->forceDelete();
                }

            }
            return $this->handleRespondSuccess('delete success', []);
        }
        return $this->handleRespondError('delete false');
    }

    public function restore(Request $request)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $request->validate([
            'ids' => 'required',
        ]);
        $post_ids = $request->input('ids');
        Post::onlyTrashed()->whereIn('id', $post_ids)->restore();
        foreach ($post_ids as $post_id) {
            $post = Post::find($post_id);
            $post->status = 'active';
            $post->save();
        }
        return $this->handleRespondSuccess('restore success', true);
    }
}


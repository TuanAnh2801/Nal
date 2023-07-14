<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\PostRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Post_meta;
use Illuminate\Http\Request;

class PostController extends BaseController
{
    public function index(Request $request)
    {
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
        $limit = request()->input('limit') ?? 20;
        $query = Post::select('*');
        if ($status) {
            $query = $query->where('status', $status);
        }
        if ($search) {
            $query = $query->where('title', 'LIKE', '%' . $search . '%');
        }
        $posts = $query->orderBy($sort_by, $sort)->paginate($limit);

        return $this->handleRespondSuccess('Get posts successfully', $posts);
    }

    public function show(Post $post)
    {
        $data = $post->categories()->where('status', '=', 'active')->get();
        return $this->handleRespondSuccess('data', $data);
    }

    public function store(PostRequest $request, Post $post)
    {
        $user = Auth::id();
        $categoryId = $request->category;
        $post->title = $request->title;
        $post->description = $request->description;
        $post->status = $request->status;
        $post->type = $request->type;
        $post->user_id = $user;
        $post->slug = Str::slug($request->title);
        $post->save();
        $post->categories()->sync($categoryId);
        if ($request->has('key') && $request->has('value')) {
            $key = $request->key;
            $value = $request->value;
            foreach ($key as $i => $metaKey) {
                $post_meta = new Post_meta();
                $post_meta->post_id = $post->id;
                $post_meta->key = $metaKey[$i];
                if (is_file($value[$i])) {
                    $imageName = Str::random(10);
                    $imagePath = $value[$i]->storeAs('public/postImage/' . date('Y/m/d'), $imageName);
                    $post_meta->value = asset(Storage::url($imagePath));
                } else {
                    $post_meta->value = $value[$i];
                }
                $post_meta->save();
            }
        }
        $post = $post->load('post_meta');
        return $this->handleRespondSuccess('create success', [
            'data' => $post,
        ]);
    }

    public function update(PostRequest $request, Post $post)
    {
        $categoryId = $request->category;
        $post->title = $request->title;
        $post->description = $request->description;
        $post->status = $request->status;
        $post->type = $request->type;
        $post->slug = Str::slug($request->title);
        $post->save();
        $post->categories()->sync($categoryId);
        if ($request->has('key') && $request->has('value')) {
            $postMetas = $post->post_meta()->get();
            $key = $request->key;
            $value = $request->value;
            foreach ($postMetas as $postMeta) {
                $valueMeta = $postMeta->value;
                if (filter_var($valueMeta, FILTER_VALIDATE_URL)) {
                    $path = 'public' . Str::after($valueMeta, 'storage');
                    Storage::delete($path);
                }
                $postMeta->delete();
            }
            foreach ($key as $i => $metaKey) {
                $post_meta = new Post_meta();
                $post_meta->post_id = $post->id;
                $post_meta->key = $metaKey[$i];
                if (is_file($value[$i])) {
                    $imageName = Str::random(10);
                    $imagePath = $value[$i]->storeAs('public/postImage/' . date('Y/m/d'), $imageName);
                    $post_meta->value = asset(Storage::url($imagePath));
                } else {
                    $post_meta->value = $value[$i];
                }
                $post_meta->save();

            }
            $post = $post->post_meta()->get();
            return $this->handleRespondSuccess('create success', [
                'data' => $post,
            ]);
        }
    }

    public function destroy(Post $post)
    {
        if ($post) {
            $postMetas = $post->post_meta()->get();
            $post->delete();
            foreach ($postMetas as $postMeta) {
                $valueMeta = $postMeta->value;
                if (filter_var($valueMeta, FILTER_VALIDATE_URL)) {
                    $path = 'public' . Str::after($valueMeta, 'storage');
                    Storage::delete($path);
                }
            }

            return $this->handleRespondSuccess('delete success', []);
        }
        return $this->handleRespondError('delete false');

    }

    public function forceDelete(PostRequest $request)
    {
        $request->validate([
            'ids' => 'required',
        ]);
        $postDe = $request->input('ids');
        $postDe = is_array($postDe) ? $postDe : [$postDe];
        Post::withTrashed()->whereIn('id', $postDe)->forceDelete();
        return $this->handleRespondSuccess('delete success', []);
    }

    public function restore(PostRequest $request)
    {
        $request->validate([
            'ids' => 'required',
        ]);
        $postRs = $request->input('ids');
        $postRs = is_array($postRs) ? $postRs : [$postRs];
        Post::onlyTrashed()->whereIn('id', $postRs)->restore();
        return $this->handleRespondSuccess('restore success', true);
    }
}

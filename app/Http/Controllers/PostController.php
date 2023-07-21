<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\PostRequest;
use App\Models\PostDetail;
use App\Models\PostMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\HasPermission;
use Stichoza\GoogleTranslate\GoogleTranslate;

class PostController extends BaseController
{
    use HasPermission;

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
        $limit = request()->input('limit') ?? config('app.paginate');
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
        $category = $post->categories()->where('status', '=', 'active')->get();
        $post_detail = $post->post_detail()->get();
        $data = [
            'category' => $category,
            'post' => $post,
            'post_detail' => $post_detail
        ];
        return $this->handleRespondSuccess('data', $data);
    }

    public function store(PostRequest $request, Post $post)
    {
        if (!Auth::user()->hasPermission('create')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $languages = ['ko', 'zh-CN', 'zh-TW', 'th', 'ja', 'vi'];
        $user = Auth::id();
        $tr = new GoogleTranslate();
        $title = $request->title;
        $status = $request->status;
        $content = $request->contents;
        $type = $request->type;
        $category_id = $request->category;
        $post->title = $title;
        $post->status = $status;
        $post->content = $content;
        $post->type = $type;
        $post->author = $user;
        $post->slug = Str::slug($title);
        $post->save();
        $post->categories()->sync($category_id);
        foreach ($languages as $language) {
            $post_detail = new PostDetail();
            $post_detail->title = $tr->setSource('en')->setTarget($language)->translate($title);
            $post_detail->content = $tr->setSource('en')->setTarget($language)->translate($content);
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
                if (is_file($value[$i])) {
                    $image_name = Str::random(10);
                    $image_path = $value[$i]->storeAs('public/postImage/' . date('Y/m/d'), $image_name);
                    $post_meta->value = asset(Storage::url($image_path));
                } else {
                    $post_meta->value = $value[$i];
                }
                $post_meta->save();
            }
        }
        $meta_data = $post->post_meta()->get();
        return $this->handleRespondSuccess('create success', [
            'post' => $post,
            'post_meta' => $meta_data
        ]);
    }

    public function update(PostRequest $request, Post $post)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $tr = new GoogleTranslate();
        $languages = ['ko', 'zh-CN', 'zh-TW', 'th', 'ja', 'vi'];
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
            $post_detail->title = $tr->setSource('en')->setTarget('ka')->translate($title);
            $post_detail->content = $tr->setSource('en')->setTarget('ka')->translate($content);
            $post_detail->content = $language;
            $post_detail->post_id = $post->id;
            $post_detail->save();
        }
        if ($request->has('key') && $request->has('value')) {
            $post_metas = $post->post_meta()->get();
            $key = $request->key;
            $value = $request->value;
            foreach ($post_metas as $post_meta) {
                $valueMeta = $post_meta->value;
                if (filter_var($valueMeta, FILTER_VALIDATE_URL)) {
                    $path = 'public' . Str::after($valueMeta, 'storage');
                    Storage::delete($path);
                }
                $post_meta->delete();
            }
            foreach ($key as $i => $metaKey) {
                $post_meta = new PostMeta();
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

            $meta_data = $post->post_meta()->get();
            return $this->handleRespondSuccess('update success', [
                'post' => $post,
                'post_meta' => $meta_data
            ]);
        }
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
                    $post_metas = $post->post_meta()->get();
                    $post->forceDelete();
                    foreach ($post_metas as $post_meta) {
                        $value_meta = $post_meta->value;
                        if (filter_var($value_meta, FILTER_VALIDATE_URL)) {
                            $path = 'public' . Str::after($value_meta, 'storage');
                            Storage::delete($path);
                        }
                    }
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


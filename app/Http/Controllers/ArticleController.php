<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleDetail;
use App\Models\Upload;
use App\Traits\HasPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ArticleRequest;
class ArticleController extends BaseController
{
    use HasPermission;

    public function index(Request $request)
    {
        $language = $request->input('language');
        $languages = config('app.languages');
        $language = in_array($language, $languages) ? $language : '';
        $status = $request->input('status');
        $layout_status = ['pending', 'published', 'reject'];
        $sort = $request->input('sort');
        $sort_types = ['desc', 'asc'];
        $sort_option = ['title', 'created_at', 'updated_at'];
        $sort_by = $request->input('sort_by');
        $status = in_array($status, $layout_status) ? $status : 'pending';
        $sort = in_array($sort, $sort_types) ? $sort : 'desc';
        $sort_by = in_array($sort_by, $sort_option) ? $sort_by : 'created_at';
        $search = $request->input('query');
        $limit = request()->input('limit') ?? config('app.paginate');
        $query = Article::select('*');
        if ($status) {
            $query = $query->where('status', $status);
        }
        if ($search) {
            $query = $query->where('title', 'LIKE', '%' . $search . '%');
        }
        if ($language) {
            $query = $query->whereHas('article_detail', function ($q) use ($language) {
                $q->where('lang', $language);
            });
            $query = $query->with(['article_detail' => function ($q) use ($language) {
                $q->where('lang', $language);
            }]);

        }
        $articles = $query->orderBy($sort_by, $sort)->paginate($limit);

        return $this->handleRespondSuccess('Get posts successfully', $articles);
    }

    public function store(ArticleRequest $request, Article $article)
    {
        if (!Auth::user()->hasPermission('create')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $id_uploads = $request->uploadId;
        $id_upload = implode(',', $id_uploads);
        $user = Auth::id();
        $languages = config('app.languages');
        $title = $request->title;
        $seo_title = $request->seo_title;
        $description = $request->description;
        $seo_description = $request->seo_description;
        $content = $request->contents;
        $category_id = $request->category;
        $article->title = $title;
        $article->seo_title = $seo_title;
        $article->description = $description;
        $article->seo_description = $seo_description;
        $article->content = $content;
        $article->user_id = $user;
        $article->upload_id = $id_upload;
        $article->slug = Str::slug($title);
        $article->save();
        if ($id_uploads) {
            foreach ($id_uploads as $id_upload) {
                $upload = Upload::find($id_upload);
                $upload->status = 'active';
                $upload->save();
            }
        }
        $upload_deletes = Upload::where('status', 'pending')->where('author', Auth::id())->get();
        foreach ($upload_deletes as $upload_delete) {
            $thumbnail = $upload_delete->thumbnail;
            $path = 'public' . Str::after($thumbnail, 'storage');
            Storage::delete($path);
        }
        Upload::where('status', 'pending')->where('author', Auth::id())->delete();
        $article->categories()->sync($category_id);
        foreach ($languages as $language) {
            $article_detail = new ArticleDetail();
            $article_detail->title = languages($language, $title);
            $article_detail->content = languages($language, $content);
            $article_detail->lang = $language;
            $article_detail->article_id = $article->id;
            $article_detail->save();
        }
        $detail_data = $article->article_detail()->get();
        return $this->handleRespondSuccess('create success', [
            'article' => $article,
            'article_data' => $detail_data
        ]);
    }


    public function show(Article $article, Request $request)
    {
        $language = $request->language;
        $category = $article->categories()->where('status', '=', 'active')->get();
        $article_detail = $article->article_detail()->where('lang', '=', $language)->get();
        $data = [
            'category' => $category,
            'article' => $article,
            'article_detail' => $article_detail
        ];
        return $this->handleRespondSuccess('data', $data);
    }

    public function update(ArticleRequest $request, Article $article)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        if ($article->status === 'pending') {
            $id_uploads = $request->uploadId;
            if ($id_uploads) {
                $id_uploadNew = implode(',', $id_uploads);
                $upload_id = $article->upload_id;
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
                $article->upload_id = $id_uploadNew;
            }
            $user = Auth::id();
            $languages = config('app.languages');
            $title = $request->title;
            $seo_title = $request->seo_title;
            $description = $request->description;
            $seo_description = $request->seo_description;
            $content = $request->contents;
            $category_id = $request->category;
            $article->title = $title;
            $article->seo_title = $seo_title;
            $article->description = $description;
            $article->seo_description = $seo_description;
            $article->status = 'pending';
            $article->content = $content;
            $article->user_id = $user;
            $article->slug = Str::slug($title);
            $article->save();
            $article->categories()->sync($category_id);
            $article->article_detail()->delete();
            foreach ($languages as $language) {
                $article_detail = new ArticleDetail();
                $article_detail->title = languages($language, $title);
                $article_detail->content = languages($language, $content);
                $article_detail->lang = $language;
                $article_detail->article_id = $article->id;
                $article_detail->save();
            }

            $detail_data = $article->article_detail()->get();
            return $this->handleRespondSuccess('update success', [
                'article' => $article,
                'article_data' => $detail_data
            ]);
        }
    }

    public function update_Detail(Request $request, Article $article)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $language = $request->language;
        $article_detail = $article->article_detail()->where('lang', $language)->first();
        if ($article_detail !== null) {
            $article_detail->title = $request->title;
            $article_detail->content = $request->contents;
            $article_detail->save();
            return $this->handleRespondSuccess('update article_detail success', $article_detail);
        }
        return $this->handleRespondError('update article_detail false');

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
        $article_delete = $request->input('ids');
        $option = $request->option;
        $articles = Article::withTrashed()->whereIn('id', $article_delete)->get();
        if ($articles) {
            foreach ($articles as $article) {
                $article->status = 'deactivate';
                $article->save();
                if ($option === 'delete') {
                    $article->delete();
                } elseif ($option === 'forceDelete') {
                    $uploads = $article->image();
                    foreach ($uploads as $upload) {
                        $thumbnail = $upload->thumbnail;
                        $path = 'public' . Str::after($thumbnail, 'storage');
                        Storage::delete($path);
                    }
                    $article->forceDelete();
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
        $article_ids = $request->input('ids');
        Article::onlyTrashed()->whereIn('id', $article_ids)->restore();
        foreach ($article_ids as $article_id) {
            $post = Article::find($article_id);
            $post->status = 'active';
            $post->save();
        }
        return $this->handleRespondSuccess('restore success', true);
    }

}

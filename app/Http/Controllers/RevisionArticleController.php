<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestoreRequest;
use App\Models\Article;
use App\Models\Revision;
use App\Models\RevisionDetail;
use App\Traits\HasPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RevisionRequest;

class RevisionArticleController extends BaseController
{
    use HasPermission;
    public function index(Request $request)
    {
        $language = $request->input('language');
        $languages = config('app.languages');
        $language = in_array($language, $languages) ? $language : '';
        $article_id = $request->input('article');
        $sort = $request->input('sort');
        $sort_types = ['desc', 'asc'];
        $sort_option = ['title', 'created_at', 'updated_at'];
        $sort_by = $request->input('sort_by');
        $sort = in_array($sort, $sort_types) ? $sort : 'desc';
        $sort_by = in_array($sort_by, $sort_option) ? $sort_by : 'created_at';
        $search = $request->input('query');
        $limit = request()->input('limit') ?? config('app.paginate');
        $query = Revision::select('*');
        if ($article_id) {
            $query = $query->where('article_id', $article_id);
        }

        if ($search) {
            $query = $query->where('title', 'LIKE', '%' . $search . '%');
        }
        if ($language) {
            $query = $query->with(['revision_detail' => function ($q) use ($language) {
                $q->where('lang', $language);
            }]);

        }
        $articles = $query->orderBy($sort_by, $sort)->paginate($limit);

        return $this->handleRespondSuccess('Get posts successfully', $articles);
    }

    public function store(Revision $revision, Article $article)
    {

        if (!Auth::user()->hasPermission('create')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $languages = config('app.languages');
        $title = $article->title;
        $description = $article->description;
        $content = $article->content;
        $revision->title = $title;
        $revision->description = $description;
        $revision->content = $content;
        $revision->article_id = $article->id;
        $revision->upload_id = $article->upload_id;
        $revision->version = $article->revision()->where('article_id', $article->id)->count() + 1;
        $revision->save();
        foreach ($languages as $language) {
            $revision_detail = new RevisionDetail();
            $revision_detail->title = languages($language, $title);
            $revision_detail->content = languages($language, $content);
            $revision_detail->lang = $language;
            $revision_detail->revision_id = $revision->id;
            $revision_detail->save();
        }
        return $this->handleRespondSuccess('create revision success', $revision);
    }

    public function show(Request $request, Article $article)
    {
        $revision = $article->revision()->get();
        $show_detail = $request->revision;
        if ($show_detail) {
            $revision = $article->revision()->where('id', $show_detail)->first();
            $revision_detail = RevisionDetail::where('revision_id', $revision->id)->get();
            $data = [
                'revision' => $revision,
                'revision_detail' => $revision_detail
            ];
            return $this->handleRespondSuccess('revision show detail', $data);
        }
        return $this->handleRespondSuccess('revision show', $revision);
    }

    public function update(RevisionRequest $request, Revision $revision)
    {
        if (!Auth::user()->hasPermission('create')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $id_uploads = $request->uploadId;
        $removal_folder= $request->removalFolder;
        if ($id_uploads) {
            $upload_id = $revision->upload_id;
            $upload_id = explode(',', $upload_id);
            $folder_is_kept = array_diff($upload_id,$removal_folder);
            handleUpload($id_uploads);
            $id_uploadNew = array_merge($folder_is_kept,$id_uploads);
            $id_uploadNew = implode(',', $id_uploadNew);
            $revision->upload_id = $id_uploadNew;
        }
        $languages = config('app.languages');
        $title = $request->title;
        $description = $request->description;
        $content = $request->contents;
        $revision->title = $title;
        $revision->description = $description;
        $revision->content = $content;
        $revision->save();
        foreach ($languages as $language) {
            $revision_detail = new RevisionDetail();
            $revision_detail->title = languages($language, $title);
            $revision_detail->content = languages($language, $content);
            $revision_detail->lang = $language;
            $revision_detail->revision_id = $revision->id;
            $revision_detail->save();
            $revision_details[] = $revision_detail;
        }
        $data = [
            'revision' => $revision,
            'revision_detail' => $revision_details
        ];
        return $this->handleRespondSuccess('update revision success', $data);
    }

    public function update_Detail(Request $request, Revision $revision)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $language = $request->language;
        $revision_detail = $revision->revision_detail()->where('lang', $language)->first();
        if ($revision_detail !== null) {
            $revision_detail->title = $request->title;
            $revision_detail->content = $request->contents;
            $revision_detail->save();
            return $this->handleRespondSuccess('update article_detail success', $revision_detail);
        }
        return $this->handleRespondError('update article_detail false');

    }

    public function review(Request $request)
    {
        $approve_id = $request->approve_id;
        $revision_approve = Revision::where('id', $approve_id)->first();
        $revision_approve->status = 'pending';
        $revision_approve->save();
        return $this->handleRespondSuccess('request has been sent', $revision_approve);
    }

    public function destroy(RestoreRequest $request)
    {
        if (!Auth::user()->hasPermission('delete')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $revision_delete = $request->input('ids');
        $revisions = Revision::whereIn('id', $revision_delete);
        if ($revisions) {
            foreach ($revisions as $revision) {
                $revision->delete();
            }
            return $this->handleRespondSuccess('delete revision success', []);
        }
        return $this->handleRespondError('delete revision false');
    }


}

<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Revision;
use App\Models\RevisionDetail;
use App\Models\Upload;
use App\Traits\HasPermission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\RevisionRequest;
class RevisionController extends BaseController
{
    use HasPermission;

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
        return $this->handleRespondSuccess('create success', $revision);
    }

    public function update(RevisionRequest $request, Revision $revision)
    {
        if (!Auth::user()->hasPermission('create')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $id_uploads = $request->uploadId;
        if ($id_uploads) {
            $id_uploadNew = implode(',', $id_uploads);
            $upload_id = $revision->upload_id;
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
                $thumbnail = $upload_useles->thumbnail;
                $path = 'public' . Str::after($thumbnail, 'storage');
                Storage::delete($path);
            }
            Upload::where('status', 'pending')->where('author', Auth::id())->delete();
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
        }
        $data = [
            'revision' => $revision,
            'revision_detail' => $revision_detail
        ];
        return $this->handleRespondSuccess('create success', $data);
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


}

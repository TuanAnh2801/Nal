<?php

namespace App\Http\Controllers;

use App\Models\TopPage;
use App\Models\TopPageDetail;
use App\Models\Upload;
use Illuminate\Http\Request;
use App\Traits\HasPermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\TopPageRequest;

class TopPageController extends BaseController
{
    use HasPermission;
    public function store(TopPageRequest $request, TopPage $topPage)
    {
        if (!Auth::user()->hasPermission('create')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        if (!$request->user()->topPage()->exists()) {
            $id_uploads = $request->uploadId;
            $id_upload = implode(',', $id_uploads);
            $user = Auth::id();
            $languages = config('app.languages');
            $company_name = $request->company_name;
            $area = $request->area;
            $summary = $request->summary;
            $about = $request->about;
            $intro_video = $request->intro_video;
            $link_website = $request->link_website;
            $link_facebook = $request->link_facebook;
            $link_instagram = $request->link_instagram;
            $topPage->company_name = $company_name;
            $topPage->area = $area;
            $topPage->summary = $summary;
            $topPage->about = $about;
            $topPage->intro_video = $intro_video;
            $topPage->link_website = $link_website;
            $topPage->link_facebook = $link_facebook;
            $topPage->link_instagram = $link_instagram;
            $topPage->user_id = $user;
            $topPage->upload_id = $id_upload;
            $topPage->save();
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
            foreach ($languages as $language) {
                $top_page_detail = new TopPageDetail();
                $top_page_detail->company_name = languages($language, $company_name);
                $top_page_detail->area = languages($language, $area);
                $top_page_detail->summary = languages($language, $summary);
                $top_page_detail->about = languages($language, $about);
                $top_page_detail->lang = $language;
                $top_page_detail->top_page_id = $topPage->id;
                $top_page_detail->save();
            }
            $detail_data = $topPage->top_pageDetail()->get();
            return $this->handleRespondSuccess('create success', [
                'topPage' => $topPage,
                'top_page_detail' => $detail_data
            ]);
        }
        return $this->handleRespondError('User already has toppage');
    }

    public function show(Request $request, TopPage $topPage)
    {
        $language = $request->language;
        $uploads = $topPage->upload_id;
        $uploads = explode(',', $uploads);
        if ($uploads) {
            foreach ($uploads as $upload) {
                $image[] = Upload::where('id', $upload)->where('type','image')->pluck('url')->first();
                $video =   Upload::where('id', $upload)->where('type','video')->pluck('url')->first();
            }
            $topPage->image = $image;
            $topPage->video = $video;
        }
        $top_page_detail = $topPage->top_pageDetail()->where('lang', $language)->first();
        return $this->handleRespondSuccess('get data success', [
            'topPage' => $topPage,
            'top_page_detail' => $top_page_detail
        ]);
    }

    public function update(TopPageRequest $request, TopPage $topPage)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $id_uploads = $request->uploadId;
        if ($id_uploads) {
            $id_uploadNew = implode(',', $id_uploads);
            $upload_id = $topPage->upload_id;
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
            $topPage->upload_id = $id_uploadNew;
        }
        $user = Auth::id();
        $languages = config('app.languages');
        $company_name = $request->company_name;
        $area = $request->area;
        $summary = $request->summary;
        $about = $request->about;
        $intro_video = $request->intro_video;
        $link_website = $request->link_website;
        $link_facebook = $request->link_facebook;
        $link_instagram = $request->link_instagram;
        $topPage->company_name = $company_name;
        $topPage->area = $area;
        $topPage->summary = $summary;
        $topPage->about = $about;
        $topPage->intro_video = $intro_video;
        $topPage->link_website = $link_website;
        $topPage->link_facebook = $link_facebook;
        $topPage->link_instagram = $link_instagram;
        $topPage->user_id = $user;
        $topPage->save();
        $topPage->top_pageDetail()->delete();
        foreach ($languages as $language) {
            $top_page_detail = new TopPageDetail();
            $top_page_detail->company_name = languages($language, $company_name);
            $top_page_detail->area = languages($language, $area);
            $top_page_detail->summary = languages($language, $summary);
            $top_page_detail->about = languages($language, $about);
            $top_page_detail->lang = $language;
            $top_page_detail->top_page_id = $topPage->id;
            $top_page_detail->save();
        }
        $detail_data = $topPage->top_pageDetail()->get();
        return $this->handleRespondSuccess('create success', [
            'topPage' => $topPage,
            'top_page_detail' => $detail_data
        ]);
    }

    public function update_Detail(Request $request, TopPage $topPage)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $language = $request->language;
        $top_page_detail = $topPage->top_pageDetail()->where('lang', $language)->first();
        if ($top_page_detail !== null) {
            $top_page_detail->title = $request->title;
            $top_page_detail->content = $request->contents;
            $top_page_detail->save();
            return $this->handleRespondSuccess('update top_page_detail success', $top_page_detail);
        }
        return $this->handleRespondError('update top_page_detail false');

    }

}

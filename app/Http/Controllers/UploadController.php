<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class UploadController extends BaseController
{
    public function create(Request $request)
    {
        $images = $request->image;
        $folder = $request->folder;
        foreach ($images as $image) {
            $url = uploadImage($image, $folder);
            $upload = new Upload();
            $upload->url = $url;
            $upload->save();
            $data[] = $upload;
        }
        return $this->handleRespondSuccess('create success', $data);
    }

    public function upload(Request $request)
    {
        $images = $request->image;
        $folder = $request->folder;
        $ids = $request->id;
        Upload::whereIn('id', $ids)->delete();
        foreach ($images as $image) {
            $url = uploadImage($image, $folder);
            $upload = new Upload();
            $upload->url = $url;
            $upload->save();
            $data[] = $upload;
        }
        return $this->handleRespondSuccess('update success', $data);
    }
}

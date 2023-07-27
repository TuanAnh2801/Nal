<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UploadController extends BaseController
{
    public function create(Request $request)
    {
        $images = $request->image;
        $folder = $request->folder;
        foreach ($images as $image) {
            $url = uploadImage($image, $folder);
            $upload = new Upload();
            $upload->thumbnail = $url;
            $upload->author = Auth::id();
            $upload->status = 'pending';
            $upload->save();
            $data[] = $upload;

        }
        return $this->handleRespondSuccess('create success', $data);
    }

}

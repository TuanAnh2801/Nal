<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function create(Request $request)
    {
        $images = $request->image;
        $folder = $request->folder;
        foreach ($images as $image) {
            $url[] = uploadImage($image, $folder);
        }
        $upload = new Upload();
        $upload->url = implode(',', $url);
        $upload->save();
        return response()->json($upload);
    }
}

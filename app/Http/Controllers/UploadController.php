<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class UploadController extends BaseController
{
    public function create(Request $request)
    {
        $videos = $request->videos;
        $images = $request->image;
        $folder = $request->folder;
        if ($images) {
            foreach ($images as $image) {
                list($url, $width) = uploadImage($image, $folder);
                $upload = new Upload();
                $upload->url = $url;
                $upload->width = $width;
                $upload->author = Auth::id();
                $upload->type = 'image';
                $upload->status = 'pending';
                $upload->save();
                $data[] = $upload;

            }
            return $this->handleRespondSuccess('Upload created successfully', $data);
        }
        elseif ($videos){
            $path = 'public/videos'  . '/' . date('Y/m/d');
            $user_id = Auth::id();
            if (!Storage::exists($path)) {
                Storage::makeDirectory($path);
            }
            foreach ($videos as $video) {
                $video_name = Str::random(10);
                $video_path = $path . '/' . $video_name;
                $video->storeAs($path, $video_name . '.' . $video->Extension());
                $upload = new Upload();
                $upload->url = asset(Storage::url($video_path));
                $upload->status = 'pending';
                $upload->author = $user_id;
                $upload->type = 'video';
                $upload->save();
                $data[] = $upload;
            }

            return $this->handleRespondSuccess($data, 'Upload created successfully');
        }
    }
}

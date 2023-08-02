<?php

use App\Models\Upload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

function handleUpload($id_uploads)
{
    foreach ($id_uploads as $id_upload) {
        $upload = Upload::find($id_upload);
        if ( $upload->status === 'pending') {
            $upload->status = 'active';
            $upload->save();
        }
    }
    $upload_deletes = Upload::where('status', 'pending')->where('author', Auth::id())->get();
    foreach ($upload_deletes as $upload_delete) {
        $thumbnail = $upload_delete->url;
        $path = 'public' . Str::after($thumbnail, 'storage');
        Storage::delete($path);
    }
    Upload::where('status', 'pending')->where('author', Auth::id())->delete();
}

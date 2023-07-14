<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Support\Str;
use App\Http\Requests\ImageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class  MediaController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $media = Media::all();
        return \response()->json(['data' => $media]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ImageRequest $request)
    {
        $avatar = $request->avatar;
        $dir = 'uploads/' . date('Y/m/d');
        $imageName = Str::random(7) . pathinfo($avatar, PATHINFO_EXTENSION);
        if (!$avatar){
            return $this->handleRespondError('create false');
        }
            $media = new Media();
            $avatar->move(public_path($dir), $imageName);
            $path = 'uploads/' . date('Y/m/d/') . $imageName;
            $url_image = url($path);
            $media->avatar = $imageName;
            $media->path = $path;
            $media->url_path = $url_image;
            $media->save();
            return $this->handleRespondSuccess('create success', $media);

    }

    /**
     * Display the specified resource.
     */
    public function show(Media $media)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Media $media, Request $request)
    {
        $dir_public = 'uploads/' . date('Y/m/d');
        $avatar = $request->avatar;
        if (!$avatar) {
            return $this->handleRespondError('please enter photo');
        }
        $imagePath = $media->path;
        $imageName = Str::random(7) . pathinfo($avatar, PATHINFO_EXTENSION);
        $path = 'uploads/' . date('Y/m/d/') . $imageName;
        $url_image = asset($path);
        $avatar->move(public_path($dir_public), $imageName);
        $media->avatar = $imageName;
        $media->path = $path;
        $media->url_path = $url_image;
        $media->save();
        File::delete($imagePath);
        return $this->handleRespondSuccess('update success', $media);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Media $media)
    {
        $delete = $media->delete();
        $imagePath = $media->path;
        File::delete($imagePath);
        if ($delete) {
            return $this->handleRespondSuccess('delete success', null);
        } else {
            return $this->handleRespondError('delete false');
        }

    }
}

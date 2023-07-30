<?php

use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

function uploadImage($image, $folder)
{
    $uploaded_image = Image::make($image);
    $input_width = $uploaded_image->getWidth();
    $input_height = $uploaded_image->getHeight();
    $resize_pattern = [
        '720x2000', '1280x2000', '480x2000', '330x2000', '200x2000', '100x2000', '300x300'
    ];
    $size = null;
    $minDistance = PHP_INT_MAX;
    $name_image = Str::random(10);
    if ($folder === 'users') {
        $path = "public/{$folder}/" . date('Y/m/d');
        if (!Storage::exists($path)) {
            Storage::makeDirectory($path);
        }
        $image_path = $path . '/' . $name_image;
        Image::make($uploaded_image)->resize(300, 300)->save(storage_path('app/' . $path . '/' . $name_image));
        $width = '300';
        return [asset(Storage::url($image_path)),$width];
    }

    foreach ($resize_pattern as $size) {
        list($width, $height) = explode('x', $size);
        $distance = abs($width - $input_width) + abs($height - $input_height);
        if ($distance < $minDistance) {
            $minDistance = $distance;
            $nearestSize = $size;
        }
    }

    list($width, $height) = explode('x', $nearestSize);

    $path = "public/{$folder}/" . date('Y/m/d');
    if (!Storage::exists($path)) {
        Storage::makeDirectory($path);
    }
    $image_path = $path . '/' . $name_image;
    Image::make($uploaded_image)->resize($width, $height)->save(storage_path('app/' . $path . '/' . $name_image));
    return [asset(Storage::url($image_path)),$width];

}

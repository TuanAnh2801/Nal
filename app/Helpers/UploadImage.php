<?php

use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

function uploadImage($image, $folder)
{
    $uploaded_image = Image::make($image);
    $input_width = $uploaded_image->getWidth();
    $input_height = $uploaded_image->getHeight();
    $resize_pattern = config('app.size_image');
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

        return asset(Storage::url($image_path));
    }

    foreach ($resize_pattern as $pattern) {
        list($width, $height) = explode('x', $pattern);
        $distance = sqrt(pow($input_width - $width, 2) + pow($input_height - $height, 2));
        if ($distance < $minDistance) {
            $minDistance = $distance;
            $size = $pattern;
        }
    }

    list($width, $height) = explode('x', $size);

    $path = "public/{$folder}/" . date('Y/m/d');
    if (!Storage::exists($path)) {
        Storage::makeDirectory($path);
    }
    $image_path = $path . '/' . $name_image;
    Image::make($uploaded_image)->resize($width, $height)->save(storage_path('app/' . $path . '/' . $name_image));
    return asset(Storage::url($image_path));
}

<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;

class   ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return \response()->json(['data'=>$products]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return \view('product.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $dir = 'uploads/'. date('Y/m/d');
        $dir1 = 'images/'. date('Y/m/d');

        $request->validate([

            'avatar' => 'required|image|mimes:png,jpg,svg|max:10240',

        ]);

        $imageName = date('Ymd') .  Str::random(2);
        $request->avatar->storeAs($dir1, $imageName);
        $request->avatar->move(public_path($dir), $imageName);
        $path = 'uploads/' . date('Y/m/d/')  . $imageName;
        $url_image = url($path);
        $fillable = Product::create([
            'avatar' => $imageName,
            'price' => $path,
            'url_path'=>$url_image

        ]);

        if ($fillable) {
            return \response()->json([
                'success' => 'create success',
                'path' => $path,
                'link' => $url_image
            ]);
        } else {
            return \response()->json(['error' => 'create false']);

        }


    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {


        $product_one = Product::all()->where('id', '=', $id);

        return \view('product.update', ['product_alone' => $product_one]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Product $product, Request $request)
    {
        $dir_public = 'uploads/'. date('Y/m/d');
        $dir_storage = 'public/images/'. date('Y/m/d');

        if ($request->hasFile('avatar')) {
            $imagePath_public = 'uploads/' . date('Y/m/d/', strtotime($product->created_at)) . $product->avatar;
            $imagePath_storage = 'storage/images/' . date('Y/m/d/', strtotime($product->created_at)) . $product->avatar;

            $imageName = date('Ymd') . Str::random(2);
            $request->avatar->storeAs($dir_storage, $imageName);
            $request->avatar->move(public_path($dir_public), $imageName);
            $update=   Product::where('id', $product->id)->update([
                'title' => $request->title,
                'avatar' => $imageName,
                'price' => $request->price,
                'amount' => $request->amount,
                'description' => $request->description,

            ]);
            File::delete($imagePath_public);
            File::delete($imagePath_storage);

            $path_public = 'uploads/' . date('Y/m/d/')  . $imageName;
            $path_storage = 'storage/images/' . date('Y/m/d/')  . $imageName;

            $url_imagePublic = asset($path_public);
            $url_imageStorage = asset($path_storage);
            return \response()->json([

                'path_public' => $path_public,
                'path_storage'=> $path_storage,
                'link_public' => $url_imagePublic,
                'link_storage'=>$url_imageStorage
            ]);
        } else {

            $update = Product::query()->where('id', $product->id)->update([
                'title' => $request->title,
                'price' => $request->price,
                'amount' => $request->amount,
                'description' => $request->description,

            ]);
            $path = 'uploads/' . date('Y/m/d/')  . $product->avatar;
            $url_image = url($path);
            return \response()->json([

                'path' => $path,
                'link' => $url_image
            ]);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $imagePath = 'uploads/' . date('Y/m/d/', strtotime($product->created_at)) . $product->avatar;
        $delete = Product::where('id', $product->id)->delete();

        File::delete($imagePath);
        if ($delete) {
            return \response()->json(['success' => 'delete success']);
        } else {
            return \response()->json(['error' => 'delete false']);


        }

    }
}

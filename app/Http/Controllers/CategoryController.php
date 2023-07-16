<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\CategoryRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $layout_status = ['active', 'deactivate'];
        $sort = $request->input('sort');
        $sort_types = ['desc', 'asc'];
        $sort_by = $request->input('sort_by');
        $sort_option = ['name', 'created_at', 'updated_at'];
        $status = in_array($status, $layout_status) ? $status : 'active';
        $sort = in_array($sort, $sort_types) ? $sort : 'desc';
        $sort_by = in_array($sort_by, $sort_option) ? $sort_by : 'created_at';
        $search = $request->input('query');
        $limit = request()->input('limit') ?? config('app.paginate');
        $query = Category::select('*');
        if ($status) {
            $query = $query->where('status', $status);
        }
        if ($search) {
            $query = $query->where('name', 'LIKE', '%' . $search . '%');
        }
        $categories = $query->orderBy($sort_by, $sort)->paginate($limit);

        return $this->handleRespondSuccess($categories, 'Get all categories');
    }

    public function show(Category $category)
    {
        $data = $category->post()->where('status', '=', 'active')->get();
        return $this->handleRespondSuccess('data', $data);
    }

    public function store(CategoryRequest $request, Category $category)
    {
        $user = Auth::id();
        $image = $request->image;
        if ($image) {
            $image_name = Str::random(10);
            $image_path = $image->storeAs('public/upload/' . date('Y/m/d'), $image_name);
            $image_url = asset(Storage::url($image_path));
            $category->url_image = $image_url;
        }
        $category->name = $request->name;
        $category->status = $request->status;
        $category->description = $request->description;
        $category->type = $request->type;
        $category->user_id = $user;
        $category->slug = Str::slug($request->name);
        $category->save();
        return $this->handleRespondSuccess('create success', $category);
    }

    public function update(CategoryRequest $request, Category $category)
    {

        $image = $request->image;
        if (!$request->hasFile('image')) {
            $category->update($request->all());
            $category->slug = Str::slug($request->name);
            return $this->handleRespondSuccess('update success', $category);
        }
        $image_name = Str::random(10);
        $path = 'public' . Str::after($category->url_image, 'storage');
        Storage::delete($path);
        $image_path = $image->storeAs('public/upload/' . date('Y/m/d'), $image_name);
        $image_url = asset(Storage::url($image_path));
        $category->name = $request->name;
        $category->description = $request->description;
        $category->type = $request->type;

        $category->slug = Str::slug($request->name);
        $category->url_image = $image_url;
        $category->save();
        return $this->handleRespondSuccess('update success', $category);
    }

    public function destroy(Category $category)
    {
        $path = 'public' . Str::after($category->url_image, 'storage');
        $category->delete();
        Storage::delete($path);
        return $this->handleRespondSuccess('delete success', []);
    }
}

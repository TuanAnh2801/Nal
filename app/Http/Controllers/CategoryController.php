<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\CategoryRequest;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Traits\HasPermission;

class CategoryController extends BaseController
{
    use HasPermission;

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
        $post = $category->post()->where('status', '=', 'active')->get();
        $data = [
          'category'=>$category,
          'post'=> $post
        ];
        return $this->handleRespondSuccess('data', $data);
    }

    public function store(CategoryRequest $request, Category $category)
    {
        if (!Auth::user()->hasPermission('create')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $user = Auth::id();
        $image = $request->image;
        if ($image) {
            $image_name = Str::random(10);
            $image_path = $image->storeAs('public/upload/' . date('Y/m/d'), $image_name);
            $image_url = asset(Storage::url($image_path));
            $category->url = $image_url;
        }
        $category->name = $request->name;
        $category->status = $request->status;
        $category->description = $request->description;
        $category->type = $request->type;
        $category->author = $user;
        $category->slug = Str::slug($request->name);
        $category->save();
        return $this->handleRespondSuccess('create success', $category);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
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
        $category->image = $image_url;
        $category->save();
        return $this->handleRespondSuccess('update success', $category);
    }

    public function destroy(Request $request)
    {
        if (!Auth::user()->hasPermission('delete')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $request->validate([
            'ids' => 'required',
            'option' => 'required|in:delete,forceDelete'
        ]);
        $category_delete = $request->input('ids');
        $option = $request->option;
        $categories = Category::withTrashed()->whereIn('id', $category_delete)->get();
        if ($categories) {
            foreach ($categories as $category) {
                if ($option === 'delete') {
                    $category->status = 'deactivate';
                    $category->save();
                    $category->delete();
                } elseif ($option === 'forceDelete') {
                    $category->forceDelete();
                }
            }
            return $this->handleRespondSuccess('delete success', []);
        }
        return $this->handleRespondError('delete false');
    }
    public function restore(Request $request)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
            $request->validate([
                'ids' => 'required',
            ]);
            $category_ids = $request->input('ids');
            Category::onlyTrashed()->whereIn('id', $category_ids)->restore();
            foreach ($category_ids as $category_id) {
                $category = Category::find($category_id);
                $category->status = 'active';
                $category->save();
            }
            return $this->handleRespondSuccess('restore success', true);
    }

}

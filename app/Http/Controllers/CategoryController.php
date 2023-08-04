<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestoreRequest;
use App\Models\Category;
use App\Http\Requests\CategoryRequest;
use App\Models\Upload;
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

    public function store(CategoryRequest $request, Category $category)
    {
        if (!Auth::user()->hasPermission('create')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $user = Auth::id();
        $id_uploads = $request->uploadId;
        $id_upload = implode(',', $id_uploads);
        $category->name = $request->name;
        $category->status = $request->status;
        $category->description = $request->description;
        $category->type = $request->type;
        $category->author = $user;
        $category->upload_id = $id_upload;
        $category->slug = Str::slug($request->name);
        $category->save();
        if ($id_uploads) {
            handleUpload($id_uploads);
        }
        return $this->handleRespondSuccess('create category success', $category);
    }

    public function show(Category $category)
    {
        $uploads = $category->upload_id;
        $uploads = explode(',', $uploads);
        if ($uploads) {
            foreach ($uploads as $upload) {
                $image[] = Upload::where('id', $upload)->pluck('url')->first();
            }
            $category->image = $image;
        }
        $post = $category->post()->where('status', '=', 'active')->get();
        $data = [
            'category' => $category,
            'post' => $post
        ];
        return $this->handleRespondSuccess('data category', $data);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $id_uploads = $request->uploadId;
        $removal_folder= $request->removalFolder;
        if ($id_uploads) {
            $upload_id = $category->upload_id;
            $upload_id = explode(',', $upload_id);
            $folder_is_kept = array_diff($upload_id,$removal_folder);
            $upload_deletes = Upload::whereIn('id', $removal_folder)->get();
            Upload::whereIn('id', $removal_folder)->delete();
            foreach ($upload_deletes as $upload_delete) {
                $url = $upload_delete->url;
                $path = 'public' . Str::after($url, 'storage');
                Storage::delete($path);
            }
            handleUpload($id_uploads);
            $id_uploadNew = array_merge($folder_is_kept,$id_uploads);
            $id_uploadNew = implode(',', $id_uploadNew);
            $category->upload_id = $id_uploadNew;
        }
        $category->name = $request->name;
        $category->description = $request->description;
        $category->type = $request->type;
        $category->slug = Str::slug($request->name);
        $category->save();
        return $this->handleRespondSuccess('update category success', $category);
    }

    public function destroy(Request $request)
    {
        if (!Auth::user()->hasPermission('delete')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
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
                    $upload_id = $category->upload_id;
                    $upload_id = explode(',', $upload_id);
                    $upload_deletes = Upload::whereIn('id', $upload_id)->get();
                    Upload::whereIn('id', $upload_id)->delete();
                    foreach ($upload_deletes as $upload_delete) {
                        $url = $upload_delete->url;
                        $path = 'public' . Str::after($url, 'storage');
                        Storage::delete($path);
                        $category->forceDelete();
                    }
                }
                return $this->handleRespondSuccess('delete category success', []);
            }
            return $this->handleRespondError('delete category false');
        }
    }

    public function restore(RestoreRequest $request)
    {
        if (!Auth::user()->hasPermission('update')) {
            return $this->handleRespondError('you do not have access')->setStatusCode(403);
        }
        $category_ids = $request->input('ids');
        Category::onlyTrashed()->whereIn('id', $category_ids)->restore();
        foreach ($category_ids as $category_id) {
            $category = Category::find($category_id);
            $category->status = 'active';
            $category->save();
        }
        return $this->handleRespondSuccess('restore category success', true);
    }

}

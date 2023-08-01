<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Post;
use App\Models\Upload;

class DashboardController extends BaseController
{
    public function dashboard()
    {
        $count_category = Category::all()->where('status', '=', 'active')->count();
        $category_hots = Category::limit(10)->where('status', 'active')->get();
        foreach ($category_hots as $category_hot) {
            $uploads = $category_hot->upload_id;
            $uploads = explode(',', $uploads);
            if ($uploads) {
                foreach ($uploads as $upload) {
                    $image[] = Upload::where('id', $upload)->pluck('url')->first();
                }
                $category_hot->image = $image;
            }
        }
        $count_post = Post::all()->where('status', '=', 'published')->count();
        $post_hots = Post::limit(10)->where('status', 'published')->get();
        foreach ($post_hots as $post_hot) {
            $uploads = $post_hot->upload_id;
            $uploads = explode(',', $uploads);
            if ($uploads) {
                foreach ($uploads as $upload) {
                    $image[] = Upload::where('id', $upload)->pluck('url')->first();
                }
                $post_hot->image = $image;
            }
        }
        $count_article = Article::all()->where('status', '=', 'published')->count();
        $article_hots = Article::limit(10)->where('status', 'published')->get();
        foreach ($article_hots as $article_hot) {
            $uploads = $article_hot->upload_id;
            $uploads = explode(',', $uploads);
            if ($uploads) {
                foreach ($uploads as $upload) {
                    $image[] = Upload::where('id', $upload)->pluck('url')->first();
                }
                $article_hot->image = $image;
            }
        }
        $data = [
            'count_category' => $count_category,
            'categories' => $category_hots,
            'count_post' => $count_post,
            'posts' => $post_hots,
            'count_article' => $count_article,
            'article' => $article_hots,
        ];
        return $this->handleRespondSuccess('get data success', $data);
    }
}

<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostDetail;
use App\Models\PostMeta;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->name();
        return [
            'title' => $title,
            'content' => fake()->text(),
            'slug' => Str::slug($title),
            'type' => 'yes',
            'status' => 'active',
            'author' => User::inRandomOrder()->pluck('id')->first(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Post $post) {
            $categoryIds = Category::inRandomOrder()->pluck('id');
            $languages = config('app.languages');
            $post->categories()->sync($categoryIds);
            foreach ($languages as $language) {
                $post_detail = new PostDetail();
                $post_detail->title = languages($language, $post->title);
                $post_detail->content = languages($language, $post->content);
                $post_detail->lang = $language;
                $post_detail->post_id = $post->id;
                $post_detail->save();
            }
            $post_meta = new PostMeta();
            $post_meta->post_id = $post->id;
            $post_meta->key = 'string';
            $post_meta->value = 'love';
            $post_meta->save();
        });
    }
}

<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();
        return [
            'name' => $name,
            'description' => fake()->text(),
            'slug' => Str::slug($name),
            'type' => fake()->text(),
            'status' => 'active',
            'author' => User::inRandomOrder()->pluck('id')->first(),
        ];
    }

}

<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Post::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->title,
            'description' => $this->faker->text(),
            'image' => $this->faker->imageUrl()
        ];
    }
}

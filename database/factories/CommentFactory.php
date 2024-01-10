<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Comment::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'text' => $this->faker->text(),
            'commentable_id' => Post::factory(),
            'commentable_type' => Post::class
        ];
    }
}

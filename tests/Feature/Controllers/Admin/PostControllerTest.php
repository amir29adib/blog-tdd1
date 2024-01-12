<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $middlewares = ['web', 'admin'];
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testIndexMethod()
    {
        $this->withoutExceptionHandling();
        Post::factory()->count(100)->create();

        $this
            ->actingAs(User::factory()->admin()->create())
            ->get(route('post.index'))
            ->assertOk()
            ->assertViewIs('admin.post.index')
            ->assertViewHas('posts', Post::latest()->paginate(15));

        $this->assertEquals(
            $this->middlewares,
            request()->route()->middleware()
        );
    }

    public function testCreateMethod()
    {
        $this->withoutExceptionHandling();
        Tag::factory()->count(20)->create();

        $this
            ->actingAs(User::factory()->admin()->create())
            ->get(route('post.create'))
            ->assertOk()
            ->assertViewIs('admin.post.create')
            ->assertViewHas('tags', Tag::latest()->get());

        $this->assertEquals(
            $this->middlewares,
            request()->route()->middleware()
        );
    }

    public function testEditMethod()
    {
        $this->withoutExceptionHandling();
        $post = Post::factory()->create();
        Tag::factory()->count(20)->create();

        $this
            ->actingAs(User::factory()->admin()->create())
            ->get(route('post.edit', $post->id))
            ->assertOk()
            ->assertViewIs('admin.post.edit')
            ->assertViewHasAll([
                'tags' => Tag::latest()->get(),
                'post' => $post
            ]);

        $this->assertEquals(
            $this->middlewares,
            request()->route()->middleware()
        );
    }

    public function testStoreMethod()
    {
        $user = User::factory()->admin()->create();
        $data = Post::factory()->state(['user_id' => $user->id])->make()->toArray();
        $tags = Tag::factory()->count(rand(1,5))->create();

        $this
            ->actingAs($user)
            ->post(
                route('post.store'),
                array_merge(
                    ['tags' => $tags->pluck('id')->toArray()],
                    $data
                )
            )
            ->assertSessionHas('message', 'new post has been created')
            ->assertRedirect(route('post.index'));

        $this->assertDatabaseHas('posts', $data);
        $this->assertEquals(
            $tags->pluck('id')->toArray(),
            Post::where($data)->first()->tags()->pluck('id')->toArray()
        );

        $this->assertEquals(
            $this->middlewares,
            request()->route()->middleware()
        );
    }

    public function testUpdateMethod()
    {
        $user = User::factory()->admin()->create();
        $data = Post::factory()->state(['user_id' => $user->id])->make()->toArray();
        $post = Post::factory()
            ->state(['user_id' => $user->id])
            ->hasTags(rand(1, 5))
            ->create();
        $tags = Tag::factory()->count(rand(1,5))->create();

        $this
            ->actingAs($user)
            ->patch(
                route('post.update', $post->id),
                array_merge(
                    ['tags' => $tags->pluck('id')->toArray()],
                    $data
                )
            )
            ->assertSessionHas('message', 'the post has been updated')
            ->assertRedirect(route('post.index'));

        $this->assertDatabaseHas('posts', array_merge(['id' => $post->id] , $data));
        $this->assertEquals(
            $tags->pluck('id')->toArray(),
            Post::where($data)->first()->tags()->pluck('id')->toArray()
        );

        $this->assertEquals(
            $this->middlewares,
            request()->route()->middleware()
        );
    }

    public function testValidationRequestRequiredData()
    {
        $user = User::factory()->admin()->create();
        $data = [];
        $errors = [
            'title' => 'The title field is required.',
            'description' => 'The description field is required.',
            'image' => 'The image field is required.',
            'tags' => 'The tags field is required.',
        ];

        // store method
        $this
            ->actingAs($user)
            ->post(route('post.store'), $data)
            ->assertSessionHasErrors($errors);

        // update method
        $this
            ->actingAs($user)
            ->patch(route('post.update', Post::factory()->create()->id), $data)
            ->assertSessionHasErrors($errors);
    }

    public function testValidationRequestDescriptionDataHasMinimumRule()
    {
        $user = User::factory()->admin()->create();
        $data = ['description' => 'lord'];
        $errors = [
            'description' => 'The description must be at least 5 characters.',
        ];

        // store method
        $this
            ->actingAs($user)
            ->post(route('post.store'), $data)
            ->assertSessionHasErrors($errors);

        // update method
        $this
            ->actingAs($user)
            ->patch(route('post.update', Post::factory()->create()->id), $data)
            ->assertSessionHasErrors($errors);
    }

    public function testValidationRequestImageDataHasUrlRule()
    {
        $user = User::factory()->admin()->create();
        $data = ['image' => 'lord'];
        $errors = [
            'image' => 'The image must be a valid URL.',
        ];

        // store method
        $this
            ->actingAs($user)
            ->post(route('post.store'), $data)
            ->assertSessionHasErrors($errors);

        // update method
        $this
            ->actingAs($user)
            ->patch(route('post.update', Post::factory()->create()->id), $data)
            ->assertSessionHasErrors($errors);
    }

    public function testValidationRequestTagsDataHasArrayRule()
    {
        $user = User::factory()->admin()->create();
        $data = ['tags' => 'lord'];
        $errors = [
            'tags' => 'The tags must be an array.',
        ];

        // store method
        $this
            ->actingAs($user)
            ->post(route('post.store'), $data)
            ->assertSessionHasErrors($errors);

        // update method
        $this
            ->actingAs($user)
            ->patch(route('post.update', Post::factory()->create()->id), $data)
            ->assertSessionHasErrors($errors);
    }

    public function testValidationRequestTagsDataMustExistInTagsTable()
    {
        $user = User::factory()->admin()->create();
        $data = ['tags' => [0]];
        $errors = [
            'tags.0' => 'The selected tags.0 is invalid.',
        ];

        // store method
        $this
            ->actingAs($user)
            ->post(route('post.store'), $data)
            ->assertSessionHasErrors($errors);

        // update method
        $this
            ->actingAs($user)
            ->patch(route('post.update', Post::factory()->create()->id), $data)
            ->assertSessionHasErrors($errors);
    }

}

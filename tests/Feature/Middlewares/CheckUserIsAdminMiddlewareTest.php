<?php

namespace Tests\Feature\Middlewares;

use App\Http\Middleware\CheckUserIsAdmin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\TestCase;

class CheckUserIsAdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testWhenUserIsNotAdmin()
    {
        $user = User::factory()->user()->create();

        $this->actingAs($user);

        $request = Request::create('/admin', 'GET');

        $middleware = new CheckUserIsAdmin();

        $response = $middleware->handle($request, function (){});

        $this->assertEquals($response->getStatusCode(), 302);
    }

    public function testWhenUserIsAdmin()
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user);

        $request = Request::create('/admin', 'GET');

        $middleware = new CheckUserIsAdmin();

        $response = $middleware->handle($request, function (){});

        $this->assertEquals($response, null);
    }

    public function testWhenUserNotLoggedIn()
    {

        $request = Request::create('/admin', 'GET');

        $middleware = new CheckUserIsAdmin();

        $response = $middleware->handle($request, function (){});

        $this->assertEquals($response->getStatusCode(), 302);
    }

}

<?php

namespace Tests\Feature\Search;

use App\Models\Biodata;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

abstract class SearchTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        // Layout links to routes owned by other modules; stub them if those modules have not landed.
        foreach (['home' => '/', 'notifications.index' => '/notifications', 'posts.create' => '/posts/create', 'profile.show' => '/me', 'account.status' => '/account'] as $name => $uri) {
            if (! Route::has($name)) {
                Route::get('/__stub'.$uri, fn () => 'stub')->name($name);
            }
        }
        app('router')->getRoutes()->refreshNameLookups();
    }

    protected function member(array $attrs = []): User
    {
        return User::factory()->create($attrs + ['phone' => '8'.fake()->unique()->numerify('#########'), 'status' => 'active']);
    }

    protected function bio(User $u, array $attrs = []): Biodata
    {
        return Biodata::factory()->forUser($u)->create($attrs);
    }
}

<?php

namespace Tests\Feature\API\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can logout when authenticated.
     */
    public function test_user_can_logout_when_authenticated(): void
    {
        $user = $this->authenticateUser();

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'User logged out successfully',
            ]);

        // We need to manually clear the authenticated user for the test
        // since the token is still valid in the current request
        $this->app['auth']->forgetGuards();
        
        // Now try to access a protected route
        $this->getJson('/api/user')
            ->assertStatus(401);
    }

    /**
     * Test unauthenticated user cannot access logout endpoint.
     */
    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }
}

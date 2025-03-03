<?php

namespace Tests\Feature\API\Todo;

use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can retrieve their todos.
     */
    public function test_authenticated_user_can_retrieve_todos(): void
    {
        $user = $this->authenticateUser();
        
        // Create todos for the authenticated user
        $todos = Todo::factory()->count(3)->create([
            'user_id' => $user->id
        ]);
        
        // Create todos for another user (should not be returned)
        Todo::factory()->count(2)->create();

        $response = $this->getJson('/api/todos');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'completed',
                        'due_date',
                        'created_at',
                    ]
                ],
            ])
            ->assertJson([
                'status' => true,
                'message' => 'Todos retrieved successfully',
            ])
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test authenticated user with no todos gets empty array.
     */
    public function test_authenticated_user_with_no_todos_gets_empty_array(): void
    {
        $this->authenticateUser();

        $response = $this->getJson('/api/todos');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
            ])
            ->assertJson([
                'status' => true,
                'message' => 'Todos retrieved successfully',
                'data' => [],
            ])
            ->assertJsonCount(0, 'data');
    }

    /**
     * Test unauthenticated user cannot retrieve todos.
     */
    public function test_unauthenticated_user_cannot_retrieve_todos(): void
    {
        $response = $this->getJson('/api/todos');

        $response->assertStatus(401);
    }
}

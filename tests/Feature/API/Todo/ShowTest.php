<?php

namespace Tests\Feature\API\Todo;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can retrieve a specific todo.
     */
    public function test_authenticated_user_can_retrieve_specific_todo(): void
    {
        $user = $this->authenticateUser();
        
        $todo = Todo::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->getJson("/api/todos/{$todo->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'completed',
                    'due_date',
                    'created_at',
                ],
            ])
            ->assertJson([
                'status' => true,
                'message' => 'Todo retrieved successfully',
                'data' => [
                    'id' => $todo->id,
                    'title' => $todo->title,
                    'description' => $todo->description,
                    'completed' => $todo->completed,
                ],
            ]);
    }

    /**
     * Test authenticated user cannot retrieve another user's todo.
     */
    public function test_authenticated_user_cannot_retrieve_another_users_todo(): void
    {
        $this->authenticateUser();
        
        // Create a todo belonging to another user
        $anotherUser = User::factory()->create();
        $todo = Todo::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        $response = $this->getJson("/api/todos/{$todo->id}");

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Todo not found',
            ]);
    }

    /**
     * Test 404 response for non-existent todo.
     */
    public function test_404_response_for_nonexistent_todo(): void
    {
        $this->authenticateUser();
        
        $response = $this->getJson('/api/todos/999999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Todo not found',
            ]);
    }

    /**
     * Test unauthenticated user cannot retrieve a todo.
     */
    public function test_unauthenticated_user_cannot_retrieve_todo(): void
    {
        $todo = Todo::factory()->create();

        $response = $this->getJson("/api/todos/{$todo->id}");

        $response->assertStatus(401);
    }
}

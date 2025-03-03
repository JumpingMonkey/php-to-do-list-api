<?php

namespace Tests\Feature\API\Todo;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can delete their todo.
     */
    public function test_authenticated_user_can_delete_todo(): void
    {
        $user = $this->authenticateUser();
        
        $todo = Todo::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson("/api/todos/{$todo->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Todo deleted successfully',
            ]);

        $this->assertDatabaseMissing('todos', [
            'id' => $todo->id,
        ]);
    }

    /**
     * Test authenticated user cannot delete another user's todo.
     */
    public function test_authenticated_user_cannot_delete_another_users_todo(): void
    {
        $this->authenticateUser();
        
        // Create a todo belonging to another user
        $anotherUser = User::factory()->create();
        $todo = Todo::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        $response = $this->deleteJson("/api/todos/{$todo->id}");

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Todo not found',
            ]);

        // Verify the todo was not deleted
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
        ]);
    }

    /**
     * Test 404 response for non-existent todo.
     */
    public function test_404_response_for_nonexistent_todo(): void
    {
        $this->authenticateUser();
        
        $response = $this->deleteJson('/api/todos/999999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Todo not found',
            ]);
    }

    /**
     * Test unauthenticated user cannot delete a todo.
     */
    public function test_unauthenticated_user_cannot_delete_todo(): void
    {
        $todo = Todo::factory()->create();

        $response = $this->deleteJson("/api/todos/{$todo->id}");

        $response->assertStatus(401);
    }
}

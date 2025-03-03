<?php

namespace Tests\Feature\API\Todo;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can update their todo.
     */
    public function test_authenticated_user_can_update_todo(): void
    {
        $user = $this->authenticateUser();
        
        $todo = Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Original Title',
            'description' => 'Original Description',
            'completed' => false,
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'completed' => true,
            'due_date' => now()->addDays(10)->toDateTimeString(),
        ];

        $response = $this->putJson("/api/todos/{$todo->id}", $updateData);

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
                'message' => 'Todo updated successfully',
                'data' => [
                    'id' => $todo->id,
                    'title' => $updateData['title'],
                    'description' => $updateData['description'],
                    'completed' => $updateData['completed'],
                ],
            ]);

        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'title' => $updateData['title'],
            'description' => $updateData['description'],
            'completed' => $updateData['completed'] ? 1 : 0,
        ]);
    }

    /**
     * Test partial update of a todo.
     */
    public function test_partial_update_of_todo(): void
    {
        $user = $this->authenticateUser();
        
        $todo = Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Original Title',
            'description' => 'Original Description',
            'completed' => false,
        ]);

        // Only update the title
        $response = $this->putJson("/api/todos/{$todo->id}", [
            'title' => 'Only Title Updated',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Todo updated successfully',
                'data' => [
                    'id' => $todo->id,
                    'title' => 'Only Title Updated',
                    'description' => $todo->description,
                    'completed' => $todo->completed,
                ],
            ]);

        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'title' => 'Only Title Updated',
            'description' => $todo->description,
            'completed' => $todo->completed ? 1 : 0,
        ]);
    }

    /**
     * Test authenticated user cannot update another user's todo.
     */
    public function test_authenticated_user_cannot_update_another_users_todo(): void
    {
        $this->authenticateUser();
        
        // Create a todo belonging to another user
        $anotherUser = User::factory()->create();
        $todo = Todo::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        $response = $this->putJson("/api/todos/{$todo->id}", [
            'title' => 'Trying to update',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Todo not found',
            ]);

        // Verify the todo was not updated
        $this->assertDatabaseMissing('todos', [
            'id' => $todo->id,
            'title' => 'Trying to update',
        ]);
    }

    /**
     * Test update validation errors.
     */
    public function test_update_validation_errors(): void
    {
        $user = $this->authenticateUser();
        
        $todo = Todo::factory()->create([
            'user_id' => $user->id,
        ]);

        // Invalid completed value
        $response = $this->putJson("/api/todos/{$todo->id}", [
            'completed' => 'not-a-boolean',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['completed']);

        // Invalid due_date format
        $response = $this->putJson("/api/todos/{$todo->id}", [
            'due_date' => 'not-a-date',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    /**
     * Test 404 response for non-existent todo.
     */
    public function test_404_response_for_nonexistent_todo(): void
    {
        $this->authenticateUser();
        
        $response = $this->putJson('/api/todos/999999', [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Todo not found',
            ]);
    }

    /**
     * Test unauthenticated user cannot update a todo.
     */
    public function test_unauthenticated_user_cannot_update_todo(): void
    {
        $todo = Todo::factory()->create();

        $response = $this->putJson("/api/todos/{$todo->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(401);
    }
}

<?php

namespace Tests\Feature\API\Todo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authenticated user can create a todo.
     */
    public function test_authenticated_user_can_create_todo(): void
    {
        $user = $this->authenticateUser();

        $todoData = [
            'title' => 'Test Todo',
            'description' => 'This is a test todo description',
            'completed' => false,
            'due_date' => now()->addDays(5)->toDateTimeString(),
        ];

        $response = $this->postJson('/api/todos', $todoData);

        $response->assertStatus(201)
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
                'message' => 'Todo created successfully',
                'data' => [
                    'title' => $todoData['title'],
                    'description' => $todoData['description'],
                    'completed' => $todoData['completed'],
                ],
            ]);

        $this->assertDatabaseHas('todos', [
            'user_id' => $user->id,
            'title' => $todoData['title'],
            'description' => $todoData['description'],
        ]);
    }

    /**
     * Test todo creation with minimal data (only required fields).
     */
    public function test_todo_creation_with_minimal_data(): void
    {
        $user = $this->authenticateUser();

        $todoData = [
            'title' => 'Minimal Todo',
        ];

        $response = $this->postJson('/api/todos', $todoData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Todo created successfully',
                'data' => [
                    'title' => $todoData['title'],
                    'completed' => false, // Default value
                ],
            ]);

        $this->assertDatabaseHas('todos', [
            'user_id' => $user->id,
            'title' => $todoData['title'],
            'completed' => 0,
        ]);
    }

    /**
     * Test todo creation validation errors.
     */
    public function test_todo_creation_validation_errors(): void
    {
        $this->authenticateUser();

        // Missing title
        $response = $this->postJson('/api/todos', [
            'description' => 'Description without title',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        // Invalid completed value
        $response = $this->postJson('/api/todos', [
            'title' => 'Test Todo',
            'completed' => 'not-a-boolean',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['completed']);

        // Invalid due_date format
        $response = $this->postJson('/api/todos', [
            'title' => 'Test Todo',
            'due_date' => 'not-a-date',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    /**
     * Test unauthenticated user cannot create a todo.
     */
    public function test_unauthenticated_user_cannot_create_todo(): void
    {
        $response = $this->postJson('/api/todos', [
            'title' => 'Test Todo',
        ]);

        $response->assertStatus(401);
    }
}

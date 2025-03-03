<?php

namespace Tests\Feature\API\Todo;

use App\Models\Todo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PaginationAndFilteringTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_paginate_todos()
    {
        $user = User::factory()->create();
        Todo::factory()->count(15)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/todos?per_page=5');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
        $response->assertJsonStructure([
            'status',
            'message',
            'data',
            'pagination' => [
                'total',
                'count',
                'per_page',
                'current_page',
                'total_pages',
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
            ],
        ]);
        
        $this->assertEquals(15, $response->json('pagination.total'));
        $this->assertEquals(5, $response->json('pagination.per_page'));
        $this->assertEquals(3, $response->json('pagination.total_pages'));
    }

    #[Test]
    public function it_can_navigate_through_pages()
    {
        $user = User::factory()->create();
        $todos = Todo::factory()->count(20)->create(['user_id' => $user->id]);
        
        // Get the second page
        $response = $this->actingAs($user)
            ->getJson('/api/todos?per_page=5&page=2');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
        $this->assertEquals(2, $response->json('pagination.current_page'));
        
        // Verify we have the correct items on page 2
        $expectedTodoIds = $todos->sortByDesc('created_at')->slice(5, 5)->pluck('id')->toArray();
        $responseTodoIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertEquals($expectedTodoIds, $responseTodoIds);
    }

    #[Test]
    public function it_can_filter_todos_by_completed_status()
    {
        $user = User::factory()->create();
        
        // Create 5 completed todos
        Todo::factory()->count(5)->create([
            'user_id' => $user->id,
            'completed' => true
        ]);
        
        // Create 7 incomplete todos
        Todo::factory()->count(7)->create([
            'user_id' => $user->id,
            'completed' => false
        ]);

        // Test filtering completed todos
        $response = $this->actingAs($user)
            ->getJson('/api/todos?completed=true');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
        
        // Test filtering incomplete todos
        $response = $this->actingAs($user)
            ->getJson('/api/todos?completed=false');

        $response->assertStatus(200);
        $response->assertJsonCount(7, 'data');
    }

    #[Test]
    public function it_can_search_todos_by_title_or_description()
    {
        $user = User::factory()->create();
        
        // Create todos with specific titles and descriptions
        Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Meeting with client',
            'description' => 'Discuss project requirements'
        ]);
        
        Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Buy groceries',
            'description' => 'Get milk, eggs, and bread'
        ]);
        
        Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Project meeting',
            'description' => 'Team sync-up'
        ]);

        // Search by title
        $response = $this->actingAs($user)
            ->getJson('/api/todos?search=meeting');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        
        // Search by description
        $response = $this->actingAs($user)
            ->getJson('/api/todos?search=milk');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    #[Test]
    public function it_handles_case_insensitive_search()
    {
        $user = User::factory()->create();
        
        Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Important Meeting',
            'description' => 'Discuss Project Requirements'
        ]);
        
        // Search with lowercase
        $response = $this->actingAs($user)
            ->getJson('/api/todos?search=important');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        
        // Search with uppercase
        $response = $this->actingAs($user)
            ->getJson('/api/todos?search=PROJECT');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        
        // Search with mixed case
        $response = $this->actingAs($user)
            ->getJson('/api/todos?search=MeEtInG');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    #[Test]
    public function it_can_filter_todos_by_due_date_range()
    {
        $user = User::factory()->create();
        
        // Create todos with specific due dates
        Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Past task',
            'due_date' => now()->subDays(5)
        ]);
        
        Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Current task',
            'due_date' => now()
        ]);
        
        Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Future task',
            'due_date' => now()->addDays(5)
        ]);

        // Filter by due date from
        $response = $this->actingAs($user)
            ->getJson('/api/todos?due_date_from=' . now()->toDateString());

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        
        // Filter by due date to
        $response = $this->actingAs($user)
            ->getJson('/api/todos?due_date_to=' . now()->toDateString());

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        
        // Filter by date range
        $response = $this->actingAs($user)
            ->getJson('/api/todos?due_date_from=' . now()->subDays(1)->toDateString() . 
                      '&due_date_to=' . now()->addDays(1)->toDateString());

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    #[Test]
    public function it_handles_specific_date_formats()
    {
        $user = User::factory()->create();
        
        // Create a todo with a specific date
        $specificDate = Carbon::create(2025, 3, 15);
        
        Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Specific date task',
            'due_date' => $specificDate
        ]);
        
        // Test with different date formats
        $formats = [
            $specificDate->format('Y-m-d'),       // 2025-03-15
            $specificDate->format('m/d/Y'),       // 03/15/2025
            $specificDate->format('d-m-Y'),       // 15-03-2025
            $specificDate->format('Y/m/d')        // 2025/03/15
        ];
        
        foreach ($formats as $format) {
            $response = $this->actingAs($user)
                ->getJson('/api/todos?due_date_from=' . $format);
                
            $response->assertStatus(200);
            $this->assertCount(1, $response->json('data'));
        }
    }

    #[Test]
    public function it_can_sort_todos()
    {
        $user = User::factory()->create();
        
        $oldestTodo = Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Oldest task',
            'created_at' => now()->subDays(5)
        ]);
        
        $middleTodo = Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Middle task',
            'created_at' => now()->subDays(3)
        ]);
        
        $newestTodo = Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Newest task',
            'created_at' => now()->subDay()
        ]);

        // Sort by created_at in ascending order (oldest first)
        $response = $this->actingAs($user)
            ->getJson('/api/todos?sort_by=created_at&sort_direction=asc');

        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertEquals($oldestTodo->id, $responseData[0]['id']);
        $this->assertEquals($newestTodo->id, $responseData[2]['id']);
        
        // Sort by created_at in descending order (newest first) - this is the default
        $response = $this->actingAs($user)
            ->getJson('/api/todos?sort_by=created_at&sort_direction=desc');

        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertEquals($newestTodo->id, $responseData[0]['id']);
        $this->assertEquals($oldestTodo->id, $responseData[2]['id']);
    }

    #[Test]
    public function it_can_sort_by_different_fields()
    {
        $user = User::factory()->create();
        
        // Create todos with different titles in alphabetical order
        $todoA = Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'A task',
            'completed' => false,
            'due_date' => now()->addDays(5)
        ]);
        
        $todoB = Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'B task',
            'completed' => true,
            'due_date' => now()->addDays(2)
        ]);
        
        $todoC = Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'C task',
            'completed' => false,
            'due_date' => now()->addDays(1)
        ]);
        
        // Sort by title ascending
        $response = $this->actingAs($user)
            ->getJson('/api/todos?sort_by=title&sort_direction=asc');

        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertEquals($todoA->id, $responseData[0]['id']);
        $this->assertEquals($todoB->id, $responseData[1]['id']);
        $this->assertEquals($todoC->id, $responseData[2]['id']);
        
        // Sort by due_date ascending
        $response = $this->actingAs($user)
            ->getJson('/api/todos?sort_by=due_date&sort_direction=asc');

        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertEquals($todoC->id, $responseData[0]['id']);
        $this->assertEquals($todoB->id, $responseData[1]['id']);
        $this->assertEquals($todoA->id, $responseData[2]['id']);
        
        // Sort by completed status
        $response = $this->actingAs($user)
            ->getJson('/api/todos?sort_by=completed&sort_direction=desc');

        $response->assertStatus(200);
        $responseData = $response->json('data');
        // The completed todo should be first
        $this->assertEquals($todoB->id, $responseData[0]['id']);
    }

    #[Test]
    public function it_validates_sort_field_and_direction()
    {
        $user = User::factory()->create();
        Todo::factory()->count(3)->create(['user_id' => $user->id]);

        // Test with invalid sort field
        $response = $this->actingAs($user)
            ->getJson('/api/todos?sort_by=invalid_field');

        $response->assertStatus(200);
        // Should default to created_at
        
        // Test with invalid sort direction
        $response = $this->actingAs($user)
            ->getJson('/api/todos?sort_direction=invalid_direction');

        $response->assertStatus(200);
        // Should default to desc
    }

    #[Test]
    public function it_combines_multiple_filters()
    {
        $user = User::factory()->create();
        
        // Create a variety of todos
        Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Important meeting',
            'completed' => true,
            'due_date' => now()->addDays(2)
        ]);
        
        Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Team meeting',
            'completed' => false,
            'due_date' => now()->addDays(1)
        ]);
        
        Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Project deadline',
            'completed' => false,
            'due_date' => now()->addDays(5)
        ]);

        // Combine multiple filters
        $response = $this->actingAs($user)
            ->getJson('/api/todos?completed=false&search=meeting&due_date_from=' . now()->toDateString());

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('Team meeting', $response->json('data.0.title'));
    }

    #[Test]
    public function it_handles_empty_search_results()
    {
        $user = User::factory()->create();
        
        // Create some todos
        Todo::factory()->count(3)->create([
            'user_id' => $user->id
        ]);
        
        // Search for something that doesn't exist
        $response = $this->actingAs($user)
            ->getJson('/api/todos?search=nonexistent');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
        $response->assertJson([
            'status' => true,
            'message' => 'Todos retrieved successfully',
            'data' => []
        ]);
    }

    #[Test]
    public function it_handles_complex_filter_combinations()
    {
        $user = User::factory()->create();
        
        // Create a variety of todos
        Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Urgent meeting',
            'description' => 'Discuss critical issues',
            'completed' => false,
            'due_date' => now()->addDays(1)
        ]);
        
        Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Urgent task',
            'description' => 'Complete project proposal',
            'completed' => true,
            'due_date' => now()->addDays(2)
        ]);
        
        Todo::factory()->create([
            'user_id' => $user->id,
            'title' => 'Regular meeting',
            'description' => 'Weekly team sync',
            'completed' => false,
            'due_date' => now()->addDays(3)
        ]);
        
        // Complex filter: search for "urgent" + incomplete + due in the next 2 days + sorted by due date
        $response = $this->actingAs($user)
            ->getJson('/api/todos?search=urgent&completed=false&due_date_from=' . now()->toDateString() . 
                      '&due_date_to=' . now()->addDays(2)->toDateString() . 
                      '&sort_by=due_date&sort_direction=asc');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('Urgent meeting', $response->json('data.0.title'));
        
        // Another complex filter: search for "meeting" + any completion status + sorted by title
        $response = $this->actingAs($user)
            ->getJson('/api/todos?search=meeting&sort_by=title&sort_direction=asc');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $this->assertEquals('Regular meeting', $response->json('data.0.title'));
        $this->assertEquals('Urgent meeting', $response->json('data.1.title'));
    }

    #[Test]
    public function it_respects_user_data_isolation()
    {
        // Create two users with their own todos
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // Create todos for user 1
        Todo::factory()->count(3)->create([
            'user_id' => $user1->id,
            'title' => 'User 1 task'
        ]);
        
        // Create todos for user 2
        Todo::factory()->count(5)->create([
            'user_id' => $user2->id,
            'title' => 'User 2 task'
        ]);
        
        // User 1 should only see their 3 todos
        $response = $this->actingAs($user1)
            ->getJson('/api/todos');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        
        // User 2 should only see their 5 todos
        $response = $this->actingAs($user2)
            ->getJson('/api/todos');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
        
        // User 1 filtering should only apply to their todos
        $response = $this->actingAs($user1)
            ->getJson('/api/todos?search=User 1');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        
        // User 1 shouldn't see User 2's todos even with search
        $response = $this->actingAs($user1)
            ->getJson('/api/todos?search=User 2');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }
}

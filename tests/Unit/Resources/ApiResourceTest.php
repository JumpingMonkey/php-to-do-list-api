<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\API\TodoCollection;
use App\Http\Resources\API\TodoResource;
use App\Http\Resources\API\UserResource;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test UserResource serialization.
     */
    public function test_user_resource_serialization(): void
    {
        $user = User::factory()->create();
        
        $resource = new UserResource($user);
        $resource->setMessage('Test message');
        
        $resourceArray = $resource->toArray(request());
        
        $this->assertEquals($user->id, $resourceArray['id']);
        $this->assertEquals($user->name, $resourceArray['name']);
        $this->assertEquals($user->email, $resourceArray['email']);
        $this->assertArrayHasKey('created_at', $resourceArray);
        
        $response = $resource->toResponse(request())->getData(true);
        
        $this->assertEquals(true, $response['status']);
        $this->assertEquals('Test message', $response['message']);
        $this->assertEquals($user->id, $response['data']['id']);
    }

    /**
     * Test TodoResource serialization.
     */
    public function test_todo_resource_serialization(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create([
            'user_id' => $user->id,
        ]);
        
        $resource = new TodoResource($todo);
        $resource->setMessage('Test todo message');
        
        $resourceArray = $resource->toArray(request());
        
        $this->assertEquals($todo->id, $resourceArray['id']);
        $this->assertEquals($todo->title, $resourceArray['title']);
        $this->assertEquals($todo->description, $resourceArray['description']);
        $this->assertEquals($todo->completed, $resourceArray['completed']);
        $this->assertArrayHasKey('due_date', $resourceArray);
        $this->assertArrayHasKey('created_at', $resourceArray);
        
        $response = $resource->toResponse(request())->getData(true);
        
        $this->assertEquals(true, $response['status']);
        $this->assertEquals('Test todo message', $response['message']);
        $this->assertEquals($todo->id, $response['data']['id']);
    }

    /**
     * Test TodoCollection serialization.
     */
    public function test_todo_collection_serialization(): void
    {
        $user = User::factory()->create();
        $todos = Todo::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);
        
        $collection = new TodoCollection($todos);
        $collection->setMessage('Test collection message');
        
        $response = $collection->toResponse(request())->getData(true);
        
        $this->assertEquals(true, $response['status']);
        $this->assertEquals('Test collection message', $response['message']);
        $this->assertCount(3, $response['data']);
        $this->assertEquals($todos[0]->id, $response['data'][0]['id']);
    }
}

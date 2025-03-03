<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Todo\StoreTodoRequest;
use App\Http\Requests\API\Todo\UpdateTodoRequest;
use App\Http\Resources\API\TodoCollection;
use App\Http\Resources\API\TodoResource;
use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $todos = $request->user()->todos()->latest()->get();

        return (new TodoCollection($todos))
            ->setMessage('Todos retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTodoRequest $request)
    {
        $todo = $request->user()->todos()->create([
            'title' => $request->title,
            'description' => $request->description,
            'completed' => $request->completed ?? false,
            'due_date' => $request->due_date
        ]);

        return (new TodoResource($todo))
            ->setMessage('Todo created successfully')
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $todo = $request->user()->todos()->find($id);

        if (!$todo) {
            return response()->json([
                'status' => false,
                'message' => 'Todo not found'
            ], 404);
        }

        return (new TodoResource($todo))
            ->setMessage('Todo retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTodoRequest $request, string $id)
    {
        $todo = $request->user()->todos()->find($id);

        if (!$todo) {
            return response()->json([
                'status' => false,
                'message' => 'Todo not found'
            ], 404);
        }

        $todo->update($request->only(['title', 'description', 'completed', 'due_date']));

        return (new TodoResource($todo))
            ->setMessage('Todo updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $todo = $request->user()->todos()->find($id);

        if (!$todo) {
            return response()->json([
                'status' => false,
                'message' => 'Todo not found'
            ], 404);
        }

        $todo->delete();

        return response()->json([
            'status' => true,
            'message' => 'Todo deleted successfully'
        ]);
    }
}

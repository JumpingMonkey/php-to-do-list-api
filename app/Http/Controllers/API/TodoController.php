<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $todos = $request->user()->todos()->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Todos retrieved successfully',
            'data' => $todos
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'nullable|boolean',
            'due_date' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $todo = $request->user()->todos()->create([
            'title' => $request->title,
            'description' => $request->description,
            'completed' => $request->completed ?? false,
            'due_date' => $request->due_date
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Todo created successfully',
            'data' => $todo
        ], 201);
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

        return response()->json([
            'status' => true,
            'message' => 'Todo retrieved successfully',
            'data' => $todo
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'nullable|boolean',
            'due_date' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $todo = $request->user()->todos()->find($id);

        if (!$todo) {
            return response()->json([
                'status' => false,
                'message' => 'Todo not found'
            ], 404);
        }

        $todo->update($request->only(['title', 'description', 'completed', 'due_date']));

        return response()->json([
            'status' => true,
            'message' => 'Todo updated successfully',
            'data' => $todo
        ]);
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

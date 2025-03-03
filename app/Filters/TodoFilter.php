<?php

namespace App\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TodoFilter extends Filter
{
    /**
     * Filter by completion status.
     *
     * @param  string|bool  $value
     * @return void
     */
    protected function completed($value): void
    {
        $this->builder->where('completed', $this->request->boolean('completed'));
    }

    /**
     * Filter by search term in title or description.
     *
     * @param  string  $value
     * @return void
     */
    protected function search($value): void
    {
        $searchTerm = $value;
        $this->builder->where(function($query) use ($searchTerm) {
            $query->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Filter by due date from.
     *
     * @param  string  $value
     * @return void
     */
    protected function due_date_from($value): void
    {
        try {
            $date = Carbon::parse($value)->startOfDay();
            $this->builder->whereDate('due_date', '>=', $date);
        } catch (\Exception $e) {
            // If date parsing fails, don't apply the filter
        }
    }

    /**
     * Filter by due date to.
     *
     * @param  string  $value
     * @return void
     */
    protected function due_date_to($value): void
    {
        try {
            $date = Carbon::parse($value)->endOfDay();
            $this->builder->whereDate('due_date', '<=', $date);
        } catch (\Exception $e) {
            // If date parsing fails, don't apply the filter
        }
    }

    /**
     * Apply sorting to the query.
     *
     * @param  string  $value
     * @return void
     */
    protected function sort_by($value): void
    {
        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['id', 'title', 'completed', 'due_date', 'created_at', 'updated_at'];
        if (!in_array($value, $allowedSortFields)) {
            $value = 'created_at';
        }
        
        $direction = $this->request->input('sort_direction', 'desc');
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';
        
        $this->builder->orderBy($value, $direction);
    }

    /**
     * Apply pagination to the query.
     *
     * @param  int  $value
     * @return void
     */
    protected function per_page($value): void
    {
        // This is handled in the controller
    }

    /**
     * Handle page parameter.
     *
     * @param  int  $value
     * @return void
     */
    protected function page($value): void
    {
        // This is handled by Laravel's paginator
    }

    /**
     * Handle sort direction parameter.
     *
     * @param  string  $value
     * @return void
     */
    protected function sort_direction($value): void
    {
        // This is handled in the sort_by method
    }
}

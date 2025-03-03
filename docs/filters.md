# Filter System Documentation

This document explains the object-oriented filter system implemented in the Todo List API.

## Overview

The filter system follows the SOLID principles of object-oriented design, specifically:

- **Single Responsibility Principle**: Each filter method handles one specific type of filtering
- **Open/Closed Principle**: The system is open for extension but closed for modification
- **Liskov Substitution Principle**: Filter subclasses can be used interchangeably
- **Interface Segregation**: Each filter method has a specific purpose
- **Dependency Inversion**: High-level modules depend on abstractions

## Components

### 1. Base Filter Class

The `Filter` abstract class provides the foundation for all filters:

```php
namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class Filter
{
    protected $request;
    protected $builder;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->getFilters() as $filter => $value) {
            if (method_exists($this, $filter)) {
                $this->$filter($value);
            }
        }

        return $this->builder;
    }

    protected function getFilters(): array
    {
        return $this->request->all();
    }
}
```

### 2. TodoFilter Implementation

The `TodoFilter` class extends the base `Filter` class and implements specific filtering methods for the Todo model:

```php
namespace App\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TodoFilter extends Filter
{
    protected function completed($value): void
    {
        $this->builder->where('completed', $this->request->boolean('completed'));
    }

    protected function search($value): void
    {
        $searchTerm = $value;
        $this->builder->where(function($query) use ($searchTerm) {
            $query->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
        });
    }

    protected function due_date_from($value): void
    {
        try {
            $date = Carbon::parse($value)->startOfDay();
            $this->builder->whereDate('due_date', '>=', $date);
        } catch (\Exception $e) {
            // If date parsing fails, don't apply the filter
        }
    }

    protected function due_date_to($value): void
    {
        try {
            $date = Carbon::parse($value)->endOfDay();
            $this->builder->whereDate('due_date', '<=', $date);
        } catch (\Exception $e) {
            // If date parsing fails, don't apply the filter
        }
    }

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
}
```

### 3. Filterable Trait

The `Filterable` trait is used to add filtering capabilities to Eloquent models:

```php
namespace App\Traits;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $query, Filter $filter): Builder
    {
        return $filter->apply($query);
    }
}
```

### 4. Model Integration

The `Todo` model uses the `Filterable` trait:

```php
namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory, Filterable;
    
    // Rest of the model...
}
```

### 5. Controller Usage

The `TodoController` injects the `TodoFilter` and applies it to the query:

```php
public function index(Request $request, TodoFilter $filter)
{
    $query = $request->user()->todos();
    
    // Apply filters
    $query = $query->filter($filter);
    
    // Paginate the results
    $perPage = $request->input('per_page', 10);
    $todos = $query->paginate($perPage);

    return (new TodoCollection($todos))
        ->setMessage('Todos retrieved successfully');
}
```

## Filter Features

### Date Filtering

The system supports flexible date filtering with the following features:

1. **Multiple Date Formats**: Accepts various date formats including:
   - ISO format (YYYY-MM-DD)
   - US format (MM/DD/YYYY)
   - European format (DD-MM-YYYY)
   - Other common formats

2. **Date Range Filtering**: Filter todos by:
   - `due_date_from`: Todos due on or after the specified date
   - `due_date_to`: Todos due on or before the specified date

3. **Robust Error Handling**: Invalid date formats are gracefully handled without breaking the application

### Search Functionality

The search feature offers:

1. **Multi-field Search**: Searches across both title and description fields
2. **Case-insensitive Matching**: Matches regardless of letter case
3. **Partial Matching**: Finds results containing the search term anywhere in the field

### Sorting Options

Sorting capabilities include:

1. **Multiple Sort Fields**: Sort by id, title, completed status, due date, or timestamps
2. **Direction Control**: Sort in ascending or descending order
3. **Security**: Validation of sort fields to prevent SQL injection

### Pagination

The API supports customizable pagination:

1. **Adjustable Page Size**: Control the number of items per page
2. **Navigation Links**: First, last, previous, and next page links
3. **Metadata**: Total items, current page, and total pages information

## Adding New Filters

To add a new filter:

1. Add a new method to the `TodoFilter` class with the name matching the query parameter
2. Implement the filtering logic in that method
3. That's it! The filter will automatically be applied when the parameter is present in the request

Example of adding a priority filter:

```php
protected function priority($value): void
{
    $this->builder->where('priority', $value);
}
```

## Benefits

This filter system offers several advantages:

1. **Maintainability**: Each filter is isolated in its own method
2. **Extensibility**: Easy to add new filters without modifying existing code
3. **Reusability**: The base filter class can be reused for other models
4. **Testability**: Each filter method can be tested in isolation
5. **Clean Controllers**: Controllers remain thin and focused on their primary responsibility
6. **Robust Error Handling**: Gracefully handles invalid inputs without breaking the application
7. **Flexible User Experience**: Supports various input formats and filtering combinations

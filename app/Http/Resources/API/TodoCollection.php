<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TodoCollection extends ResourceCollection
{
    protected $status = true;
    protected $message = '';

    /**
     * Set the status for the response.
     *
     * @param  bool  $status
     * @return $this
     */
    public function setStatus(bool $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Set the message for the response.
     *
     * @param  string  $message
     * @return $this
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        $pagination = [];
        
        // Add pagination metadata if the resource is paginated
        if ($this->resource instanceof \Illuminate\Pagination\AbstractPaginator) {
            $pagination = [
                'pagination' => [
                    'total' => $this->resource->total(),
                    'count' => $this->resource->count(),
                    'per_page' => $this->resource->perPage(),
                    'current_page' => $this->resource->currentPage(),
                    'total_pages' => $this->resource->lastPage(),
                    'links' => [
                        'first' => $this->resource->url(1),
                        'last' => $this->resource->url($this->resource->lastPage()),
                        'prev' => $this->resource->previousPageUrl(),
                        'next' => $this->resource->nextPageUrl(),
                    ],
                ],
            ];
        }
        
        return array_merge([
            'status' => $this->status,
            'message' => $this->message,
        ], $pagination);
    }
}

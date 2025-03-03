<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseApiResource extends JsonResource
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
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
        ];
    }
}

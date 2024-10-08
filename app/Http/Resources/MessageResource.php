<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sender' => $this->sender,
            'message' => $this->message,
            'message_type' => $this->message_type,
            'responded_by' => $this->responded_by,
            'expert' => $this->responded_by === 'expert' ? new ExpertResource($this->expert) : null, // Add expert details if available
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}

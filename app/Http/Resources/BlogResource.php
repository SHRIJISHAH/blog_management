<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'image_url'   => $this->image ? asset('storage/' . $this->image) : null,
            'likes_count' => $this->likes_count,
            'is_liked'    => $this->is_liked ?? false, 
            'user'        => [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ],
            'created_at'  => $this->created_at->toDateTimeString(),
        ];
    }
}
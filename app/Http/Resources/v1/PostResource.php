<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'image' => $this->image,
            'author' => [
                'name' => $this->user->name,
                'username' => $this->user->username,
            ],
            'created_at' => $this->created_at
        ];
    }
}

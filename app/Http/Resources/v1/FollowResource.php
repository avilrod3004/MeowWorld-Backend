<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FollowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'follower' => [
                'id' => $this->follower->id,
                'name' => $this->follower->name,
                'username' => $this->follower->username,
            ],
            'followed' => [
                'id' => $this->followed->id,
                'name' => $this->followed->name,
                'username' => $this->followed->username,
            ]
        ];
    }
}

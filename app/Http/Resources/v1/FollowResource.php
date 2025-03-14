<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="FollowResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="follower", type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="John Doe"),
 *         @OA\Property(property="username", type="string", example="john_doe")
 *     ),
 *     @OA\Property(property="followed", type="object",
 *         @OA\Property(property="id", type="integer", example=2),
 *         @OA\Property(property="name", type="string", example="Jane Smith"),
 *         @OA\Property(property="username", type="string", example="jane_smith")
 *     )
 * )
 */
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

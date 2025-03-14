<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="PostResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="description", type="string", example="Un post interesante sobre gatos"),
 *     @OA\Property(property="image", type="string", example="https://example.com/post-image.jpg"),
 *     @OA\Property(
 *         property="author",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="John Doe"),
 *         @OA\Property(property="username", type="string", example="johndoe"),
 *         @OA\Property(property="img_profile", type="string", example="https://example.com/profile.jpg")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-01T10:00:00Z")
 * )
 */

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
                'id' => $this->user->id,
                'name' => $this->user->name,
                'username' => $this->user->username,
                'img_profile' => $this->user->img_profile,
            ],
            'created_at' => $this->created_at
        ];
    }
}

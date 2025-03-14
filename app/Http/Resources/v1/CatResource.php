<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CatResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Fluffy"),
 *     @OA\Property(property="description", type="string", example="Un gato juguetón y cariñoso"),
 *     @OA\Property(property="image", type="string", example="https://example.com/image.jpg"),
 *     @OA\Property(property="en_adopcion", type="boolean", example=true),
 *     @OA\Property(
 *         property="owner",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="username", type="string", example="gato_adoptivo")
 *     )
 * )
 */

class CatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'en_adopcion' => $this->en_adopcion,
            'owner' => [
                'id' => $this->user->id,
                'username' => $this->user->username,
            ]
        ];
    }
}

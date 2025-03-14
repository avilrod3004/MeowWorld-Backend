<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Comentario",
 *     type="object",
 *     required={"text", "user_id", "post_id"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID del comentario",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="text",
 *         type="string",
 *         description="Texto del comentario",
 *         example="Este es un comentario sobre el post."
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="ID del usuario que hizo el comentario",
 *         example=2
 *     ),
 *     @OA\Property(
 *         property="post_id",
 *         type="integer",
 *         description="ID del post asociado con el comentario",
 *         example=5
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha y hora en la que el comentario fue creado",
 *         example="2025-03-14T15:30:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha y hora de la última actualización del comentario",
 *         example="2025-03-14T15:30:00Z"
 *     )
 * )
 */

class Comentario extends Model {
    use HasFactory;

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function post() {
        return $this->belongsTo(Post::class);
    }
}

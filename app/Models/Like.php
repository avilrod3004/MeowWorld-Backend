<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Like",
 *     type="object",
 *     required={"user_id", "post_id"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID del like",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="ID del usuario que dio el like",
 *         example=2
 *     ),
 *     @OA\Property(
 *         property="post_id",
 *         type="integer",
 *         description="ID del post al que se le dio like",
 *         example=5
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha y hora en la que se dio el like",
 *         example="2025-03-14T15:30:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha y hora de la última actualización del like",
 *         example="2025-03-14T15:30:00Z"
 *     )
 * )
 */

class Like extends Model {
    use HasFactory;

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo {
        return $this->belongsTo(Post::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *     schema="Post",
 *     type="object",
 *     title="Post",
 *     description="Modelo que representa un post en la aplicación.",
 *     required={"description", "user_id"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID único del post",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Descripción del post",
 *         example="Este es un post sobre gatos"
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         description="URL de la imagen asociada al post",
 *         example="https://res.cloudinary.com/yourapp/posts/post1.jpg"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="ID del usuario que ha creado el post",
 *         example=5
 *     )
 * )
 */
class Post extends Model {
    use HasFactory;

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany {
        return $this->hasMany(Like::class, 'post_id');
    }
}

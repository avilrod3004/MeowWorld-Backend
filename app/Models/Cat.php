<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Cat",
 *     type="object",
 *     title="Cat",
 *     description="Modelo que representa un gato en la aplicación.",
 *     required={"name", "description", "user_id"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID único del gato",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Nombre del gato",
 *         example="Fluffy"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Descripción del gato",
 *         example="Gato juguetón y cariñoso"
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         description="URL de la imagen del gato",
 *         example="https://res.cloudinary.com/yourapp/cats/cat1.jpg"
 *     ),
 *     @OA\Property(
 *         property="en_adopcion",
 *         type="boolean",
 *         description="Indica si el gato está disponible para adopción",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="ID del usuario propietario del gato",
 *         example=5
 *     )
 * )
 */
class Cat extends Model {
    use HasFactory;

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}

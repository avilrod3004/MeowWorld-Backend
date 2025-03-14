<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     required={"id", "name", "email", "password", "created_at", "updated_at"},
 *     @OA\Property(property="id", type="integer", description="ID del usuario"),
 *     @OA\Property(property="name", type="string", description="Nombre del usuario"),
 *     @OA\Property(property="username", type="string", description="Nombre de usuario único"),
 *     @OA\Property(property="email", type="string", format="email", description="Correo electrónico del usuario"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", description="Fecha de verificación del email"),
 *     @OA\Property(property="password", type="string", format="password", description="Contraseña del usuario"),
 *     @OA\Property(property="description", type="string", description="Descripción del usuario"),
 *     @OA\Property(property="img_profile", type="string", format="uri", description="URL de la imagen de perfil"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Fecha de creación"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Fecha de última actualización")
 * )
*/
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'description',
        'img_profile',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function roles() {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($role) {
        return $this->roles->contains('name', $role);
    }

    public function followers() {
        return $this->hasMany(Follow::class, 'followed_id');
    }

    public function following() {
        return $this->hasMany(Follow::class, 'follower_id');
    }
}

<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\FollowRequest;
use App\Http\Resources\v1\FollowResource;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @OA\Tag(
 *     name="Follows",
 *     description="Operaciones relacionadas con el seguimiento de usuarios"
 * )
 */
class FollowController extends Controller {

    /**
     * Obtener los usuarios que sigue
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Get(
     *     path="/api/v1/follows/{id}/followers",
     *     summary="Obtener los usuarios que siguen a un usuario",
     *     tags={"Follows"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de seguidores",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/FollowResource"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Usuario no encontrado")
     *         )
     *     )
     * )
    */
    public function getFollowers($id) {
        $user = User::find($id);

        if (!$user) {
            throw new ModelNotFoundException("Usuario no encontrado");
        }

        $followers = $user->followers;

        return response()->json([
            'status' => true,
            'data' => FollowResource::collection($followers)
        ]);

    }

    /**
     * Obtener los usuarios a los que sigue
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Get(
     *     path="/api/v1/follows/{id}/following",
     *     summary="Obtener los usuarios a los que un usuario sigue",
     *     tags={"Follows"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuarios seguidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/FollowResource"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Usuario no encontrado")
     *         )
     *     )
     * )
     */
    public function getFollowing($id) {
        $user = User::find($id);

        if (!$user) {
            throw new ModelNotFoundException("Usuario no encontrado");
        }

        $following = $user->following;

        return response()->json([
            'status' => true,
            'data' => FollowResource::collection($following)
        ]);
    }


    /**
     * @OA\Get(
     *     path="/api/v1/follows/{id}/is-following",
     *     summary="Verificar si un usuario sigue a otro usuario",
     *     tags={"Follows"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario a verificar si está siendo seguido",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verificación de seguimiento",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="isFollowing", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function isFollowing($id): JsonResponse {
        $user = auth()->user();
        $isFollowing = $user->following()->where('followed_id', $id)->exists();

        return response()->json([
            'status' => true,
            'isFollowing' => $isFollowing
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/follows/{id}/is-followed",
     *     summary="Verificar si un usuario es seguido por otro usuario",
     *     tags={"Follows"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario a verificar si está siendo seguido",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verificación de si el usuario es seguido",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="isFollowed", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function isFollowed($id): JsonResponse {
        $user = auth()->user();
        $isFollowed = $user->followers()->where('follower_id', $id)->exists();

        return response()->json([
            'status' => true,
            'isFollowed' => $isFollowed
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/v1/follows",
     *     summary="Seguir a un usuario",
     *     tags={"Follows"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"followed_id"},
     *             @OA\Property(property="followed_id", type="integer", description="ID del usuario a seguir", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario seguido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usuario seguido")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al seguir al usuario",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se pudo seguir al usuario")
     *         )
     *     )
     * )
     */
    public function store(FollowRequest $request) {
        $follower = Auth::user();
        $followed = User::find($request->input('followed_id'));

        if (!$followed) {
            throw new ModelNotFoundException("El usuario al que quiere seguir no existe.");
        }

        $followQuery = Follow::where('follower_id', $follower->id)
            ->where('followed_id', $request->input('followed_id'))
            ->first();

        if ($followQuery) {
            throw new HttpException(500, "Ya sigues a ese usuario");
        }

        $follow = new Follow();
        $follow->follower()->associate($follower);
        $follow->followed()->associate($followed);

        $res = $follow->save();

        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'Usuario seguido',
            ], 200);
        } else {
            throw new HttpException(500, "No se pudo seguir al usuario");
        }

    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    /**
     * @OA\Delete(
     *     path="/api/v1/follows/{id}",
     *     summary="Dejar de seguir a un usuario",
     *     tags={"Follows"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario al que dejar de seguir",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario dejado de seguir exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Has dejado de seguir a este usuario")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al dejar de seguir al usuario",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se pudo dejar de seguir al usuario")
     *         )
     *     )
     * )
     */
    public function destroy($id) {
        $user = auth()->user();

        $follow = Follow::where('follower_id', $user->id)
            ->where('followed_id', $id)
            ->first();

        if (!$follow) {
            throw new ModelNotFoundException("No estás siguiendo a este usuario");
        }

        $res = $follow->delete();

        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'Has dejado de seguir a este usuario'
            ], 200);
        } else {
            throw new HttpException(500, "No se pudo dejar de seguir al usuario..");
        }
    }
}

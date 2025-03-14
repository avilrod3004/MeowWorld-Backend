<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\LikeRequest;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @OA\Tag(
 *     name="Likes",
 *     description="Operaciones relacionadas con los likes en los posts"
 * )
 */
class LikeController extends Controller {

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/v1/likes",
     *     summary="Registrar un like en un post",
     *     tags={"Likes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"post_id"},
     *             @OA\Property(property="post_id", type="integer", description="ID del post al que se le dará like", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Like registrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Like registrado exitosamente.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al registrar el like",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se pudo registrar el like.")
     *         )
     *     )
     * )
     */
    public function store(LikeRequest $request) {
        $user = Auth::user();
        $post = Post::find($request->input('post_id'));

        if (!$post) {
            throw new ModelNotFoundException("El post al que quiere dar like no existe.");
        }

        $likeQuery = Like::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($likeQuery) {
            throw new HttpException(500, 'Ya has dado like a este post.');
        }

        $like = new Like();
        $like->user()->associate($user);
        $like->post()->associate($post);

        $res = $like->save();

        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'Like registrado exitosamente.',
            ], 200);
        } else {
            throw new HttpException(500,"No se pudo registrar el like.");
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/likes/{id}/is-liked",
     *     summary="Verificar si el usuario ha dado like a un post",
     *     tags={"Likes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del post",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verificación del like",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="isLiked", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Post no encontrado.")
     *         )
     *     )
     * )
     */
    public function isLikedByUser($id): JsonResponse {
        $user = Auth::user();
        $post = Post::find($id);

        if (!$post) {
            throw new ModelNotFoundException("Post no encontrado");
        }

        $isLiked = $post->likes()->where('post_id', $id)
            ->where('user_id', $user->id)
            ->exists();

        return response()->json([
            'status' => true,
            'isLiked' => $isLiked
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/likes/{id}/count",
     *     summary="Contar los likes de un post",
     *     tags={"Likes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del post",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Número de likes",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="likes", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Post no encontrado.")
     *         )
     *     )
     * )
     */
    public function countPostLikes($id): JsonResponse {
        $post = Post::find($id);

        $likes = $post->likes()->count();

        return response()->json([
            'status' => true,
            'likes' => $likes
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/v1/likes/{id}",
     *     summary="Eliminar un like de un post",
     *     tags={"Likes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del post",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Like eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Like eliminado exitosamente.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al eliminar el like",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se pudo eliminar el like.")
     *         )
     *     )
     * )
     */
    public function destroy($id) {
        $user = Auth::user();

        $like = Like::where('post_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$like) {
            throw new ModelNotFoundException("No has dado like a este post.");
        }

        $res = $like->delete();

        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'Like eliminado exitosamente.',
            ]);
        } else {
            throw new HttpException(500, "No se pudo eliminar el like.");
        }
    }
}

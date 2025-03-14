<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\UpdatePostRequest;
use App\Http\Resources\v1\PostResource;
use App\Models\Post;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\v1\PostRequest;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PostController extends Controller {
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/v1/posts",
     *     summary="Obtiene todos los posts",
     *     tags={"Posts"},
     *     @OA\Response(
     *         response=200,
     *         description="Listado de todos los posts",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Post")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="total", type="integer", example=100)
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse {
        $posts = Post::orderBy('created_at', 'desc')->get();
        $totalPosts = Post::count();

        return response()->json([
            'status' => true,
            'data' => PostResource::collection($posts),
            'meta' => [
                'total' => $totalPosts,
            ]
        ], 200);
    }

    /**
     * Display a listing of the posts by a specific user.
     */
    /**
     * @OA\Get(
     *     path="/api/v1/posts/user/{userId}",
     *     summary="Obtiene todos los posts de un usuario específico",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="ID del usuario cuyo posts se desean obtener",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de posts del usuario",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Post")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error en el servidor"
     *     )
     * )
     */

    public function getUserPosts($userId): JsonResponse {
        $posts = Post::where('user_id', $userId)->orderBy('created_at', 'desc')->get();
        $totalResults = $posts->count();

        return response()->json([
            'status' => true,
            'data' => PostResource::collection($posts),
            'meta' => [
                'total' => $totalResults,
            ],
        ], 200);
    }

    public function store(PostRequest $request): JsonResponse {
        $user = Auth::user();

        $post = new Post();
        $post->description = $request->input('description');
        $post->user()->associate($user);

        if ($request->hasFile('image')) {
            $result = Cloudinary::upload($request->file('image')->getRealPath());

            if (!$result) {
                throw new HttpException(500,"No se pudo crear el post. Error al guardar la imagen.");
            }

            $post->image = $result->getSecurePath();
        }

        $res = $post->save();

        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'Post creado correctamente',
                'data' => new PostResource($post)
            ], 201);
        } else {
            throw new HttpException(500,"No se pudo crear el post");
        }
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     path="/api/v1/posts/{id}",
     *     summary="Obtiene un post por ID",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post no encontrado"
     *     )
     * )
     */
    public function show($id): JsonResponse {
        $post = Post::find($id);

        if (!$post) {
            throw new ModelNotFoundException("Post no encontrado");
        }

        return response()->json([
            'status' => true,
            'data' => new PostResource($post)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     * @throws AuthorizationException
     */
    /**
     * @OA\Put(
     *     path="/api/v1/posts/{id}",
     *     summary="Actualiza un post",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/PostRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post actualizado correctamente",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso no autorizado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post no encontrado"
     *     )
     * )
     */
    public function update(UpdatePostRequest $request, $id): JsonResponse {
        $post = Post::find($id);

        if (!$post) {
            throw new ModelNotFoundException("Post no encontrado");
        }

        if (Auth::user()->id !== $post->user_id) {
            throw new AuthorizationException();
        }

        $post->description = $request->input('description');

        $res = $post->save();

        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'Post actualizado correctamente',
                'data' => new PostResource($post)
            ], 200);
        } else {
            throw new HttpException(500,"No se pudo actualizar el post");
        }
    }


    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    /**
     * @OA\Delete(
     *     path="/api/v1/posts/{id}",
     *     summary="Elimina un post",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del post",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post eliminado correctamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Post eliminado correctamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acceso no autorizado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post no encontrado"
     *     )
     * )
     */
    public function destroy($id): JsonResponse {
        $post = Post::find($id);

        if (!$post) {
            throw new ModelNotFoundException("Post no encontrado");
        }

        if (Auth::user()->id !== $post->user_id && !Auth::user()->hasRole("admin")) {
            throw new AuthorizationException();
        }

        $res = $post->delete();

        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'Post eliminado correctamente'
            ], 200);
        } else {
            throw new HttpException(500,"No se pudo eliminar el post");
        }
    }
}

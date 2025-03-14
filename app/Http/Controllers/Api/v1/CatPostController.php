<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\CatPostRequest;
use App\Http\Resources\v1\CatResource;
use App\Http\Resources\v1\PostResource;
use App\Models\CatPost;
use App\Models\Post;
use App\Models\Cat;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @OA\Tag(
 *     name="CatPosts",
 *     description="Relaciones entre gatos y posts"
 * )
 */
class CatPostController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/posts/{id}/cats",
     *     summary="Obtener los gatos de un post",
     *     tags={"CatPosts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del post",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Listado de gatos en el post",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/CatResource"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Post no encontrado")
     *         )
     *     )
     * )
     */
    public function getPostCats($id): JsonResponse {
        $post = Post::find($id);

        if (!$post) {
            throw new ModelNotFoundException("Post no encontrado");
        }

        $relations = CatPost::where('post_id', $id)->pluck('cat_id');
        $cats = Cat::whereIn('id', $relations)->get();

        return response()->json([
            'status' => true,
            'data' => CatResource::collection($cats)
        ]);
    }


    /**
     * @OA\Get(
     *     path="/api/v1/cats/{id}/posts",
     *     summary="Obtener los posts de un gato",
     *     tags={"CatPosts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del gato",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Listado de posts que contienen al gato",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/PostResource"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Gato no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Gato no encontrado")
     *         )
     *     )
     * )
     */
    public function getCatPosts($id): JsonResponse {
        $cat = Cat::find($id);

        if (!$cat) {
            throw new ModelNotFoundException("Gato no encontrado");
        }

        $relations = CatPost::where('cat_id', $id)->pluck('post_id');
        $posts = Post::whereIn('id', $relations)->get();

        return response()->json([
            'status' => true,
            'data' => PostResource::collection($posts)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/v1/catposts",
     *     summary="Crear una relación entre un gato y un post",
     *     tags={"CatPosts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"cat_id", "post_id"},
     *             @OA\Property(property="cat_id", type="integer", example=1),
     *             @OA\Property(property="post_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="El gato y el post ha sido relacionados.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al registrar la relación",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se pudo registrar la relación")
     *         )
     *     )
     * )
     */
    public function store(CatPostRequest $request) {
        $cat = Cat::find($request->input('cat_id'));
        $post = Post::find($request->input('post_id'));

        if (!$cat) {
            throw new ModelNotFoundException("El gato no existe en la base de datos");
        }

        if (!$post) {
            throw new ModelNotFoundException("El post no existe en la base de datos");
        }

        $relationQuery = CatPost::where('cat_id', $cat->id)
            ->where('post_id', $post->id)
            ->first();

        if ($relationQuery) {
            throw new HttpException(500, "Ya esta registrado que el gato aparece en ese post");
        }

        $catPost = new CatPost();
        $catPost->cat()->associate($cat);
        $catPost->post()->associate($post);

        $res = $catPost->save();

        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'El gato y el post ha sido relacionados.',
            ]);
        } else {
            throw new HttpException(500, "No se puedo registrar la relacion");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/v1/catposts",
     *     summary="Eliminar la relación entre un gato y un post",
     *     tags={"CatPosts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"cat_id", "post_id"},
     *             @OA\Property(property="cat_id", type="integer", example=1),
     *             @OA\Property(property="post_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Relación eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="La relación ha sido eliminada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al eliminar la relación",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No se pudo eliminar la relación")
     *         )
     *     )
     * )
     */
    public function destroy(CatPostRequest $request) {
        $cat = Cat::find($request->input('cat_id'));
        $post = Post::find($request->input('post_id'));

        if (!$cat) {
            throw new ModelNotFoundException("El gato no existe en la base de datos");
        }

        if (!$post) {
            throw new ModelNotFoundException("El post no existe en la base de datos");
        }

        $relationQuery = CatPost::where('cat_id', $cat->id)
            ->where('post_id', $post->id)
            ->first();

        if (!$relationQuery) {
            throw new HttpException(500, "Esa relacion no esta registrada en la base de datos");
        }

        $res = $relationQuery->delete();

        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'La relación ha sido borrado.',
            ]);
        } else {
            throw new HttpException(500, "No se puedo eliminar la relacion");
        }
    }
}

<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\PostResource;
use App\Models\Post;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\v1\PostRequest;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse {
        $posts = Post::latest()->paginate();
        return response()->json([
            'status' => true,
            'data' => PostResource::collection($posts),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'from' => $posts->firstItem(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
            'links' => [
                'first' => $posts->url(1),
                'last' => $posts->url($posts->lastPage()),
                'prev' => $posts->previousPageUrl(),
                'next' => $posts->nextPageUrl(),
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostRequest $request): JsonResponse {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Crear un nuevo post
        $post = new Post();
        $post->description = $request->input('description');
        $post->user()->associate($user);

        // Subir la imagen obligatoria
        if ($request->hasFile('image')) {
            $result = Cloudinary::upload($request->file('image')->getRealPath());
            $post->image = $result->getSecurePath();
        } else {
            return response()->json([
                'status' => false,
                'message' => 'La imagen es obligatoria para crear un post',
            ], 422);
        }

        // Guardar el post en la base de datos
        $res = $post->save();

        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'Post creado correctamente',
                'data' => new PostResource($post)
            ], 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'No se pudo crear el post',
        ], 500);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'status' => false,
                'errors' => 'Usuario no encontrado'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => new PostResource($post)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'status' => false,
                'errors' => 'Post no encontrado'
            ], 404);
        }

        if (Auth::user()->id !== $post->user_id) {
            return response()->json([
                'status' => false,
                'message' => 'No tines permisos para realizar esta accion'
            ], 403);
        }

        $request->validate([
            'description' => 'required|max:2000',
        ]);

        $post->description = $request->input('description');

        $res = $post->save();
        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'Post actualizado correctamente',
                'data' => new PostResource($post)
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No se pudo actualizar el post',
            ], 400);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'status' => false,
                'errors' => 'Post no encontrado'
            ], 404);
        }

        if (Auth::user()->id !== $post->user_id) {
            return response()->json([
                'status' => false,
                'errors' => 'No tienes permisos para realizar esta accion'
            ], 401);
        }

        $res = $post->delete();

        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'Post eliminado correctamente'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No se pudo eliminar el post',
            ], 400);
        }
    }
}

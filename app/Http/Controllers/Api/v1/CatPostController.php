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

class CatPostController extends Controller
{
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

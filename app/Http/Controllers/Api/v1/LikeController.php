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

class LikeController extends Controller {

    /**
     * Store a newly created resource in storage.
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

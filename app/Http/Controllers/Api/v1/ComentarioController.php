<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\ComentarioRequest;
use App\Http\Resources\v1\ComentarioResource;
use App\Models\Comentario;
use App\Models\Post;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ComentarioController extends Controller {
    public function index() {
        $comments = Comentario::orderBy('created_at', 'desc')->get();
        $totalResults = $comments->count();

        return response()->json([
            'status' => true,
            'data' => ComentarioResource::collection($comments),
            'meta' => [
                'total' => $totalResults,
            ],
        ], 200);
    }

    public function getPostComentarios($post_id) {
        $comments = Comentario::where('post_id', $post_id)->orderBy('created_at', 'desc')->get();
        $totalResults = $comments->count();

        return response()->json([
            'status' => true,
            'data' => ComentarioResource::collection($comments),
            'meta' => [
                'total' => $totalResults,
            ],
        ], 200);
    }

    public function getUserComentarios($user_id) {
        $comments = Comentario::where('user_id', $user_id)->orderBy('created_at', 'desc')->get();
        $totalResults = $comments->count();

        return response()->json([
            'status' => true,
            'data' => ComentarioResource::collection($comments),
            'meta' => [
                'total' => $totalResults,
            ],
        ], 200);
    }

    public function show($id) {
        $comment = Comentario::find($id);

        if (!$comment) {
            throw new ModelNotFoundException("Commentario no encontrado");
        }

        return response()->json([
            'status' => true,
            'data' => new ComentarioResource($comment),
        ], 200);

    }

    public function store(ComentarioRequest $request) {
        $user = Auth::user();
        $post = Post::find($request->input('post_id'));

        if (!$post) {
            throw new ModelNotFoundException("Post no encontrado");
        }

        $comment = new Comentario();
        $comment->text = $request->input('text');
        $comment->user()->associate($user);
        $comment->post()->associate($post);

        $res = $comment->save();

        if ($res) {
            return response()->json([
                'status' => true,
                'data' => new ComentarioResource($comment),
            ], 201);
        } else {
            throw new HttpException(500,"Error al guardar el comentario");
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy($id) {
        $comment = Comentario::find($id);

        if (!$comment) {
            throw new ModelNotFoundException("Commentario no encontrado");
        }

        if (Auth::user()->id !== $comment->user_id && !Auth::user()->hasRole("admin")) {
            throw new AuthorizationException();
        }

        $res = $comment->delete();

        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'Comentario eliminado correctamente',
            ], 200);
        } else {
            throw new HttpException(500, "No se puedo eliminar el comentario");
        }
    }
}

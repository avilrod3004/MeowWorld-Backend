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

class FollowController extends Controller {

    /**
     * Obtener los usuarios que sigue
     * @param $id
     * @return \Illuminate\Http\JsonResponse
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


    public function isFollowing($id): JsonResponse {
        $user = auth()->user();
        $isFollowing = $user->following()->where('followed_id', $id)->exists();

        return response()->json([
            'status' => true,
            'isFollowing' => $isFollowing
        ], 200);
    }

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
    public function store(FollowRequest $request) {
        $follower = Auth::user();
        $followed = User::find($request->input('followed_id'));

        if (!$followed) {
            throw new ModelNotFoundException("El usuario al que quiere seguir no existe.");
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
            throw new HttpException("No se pudo seguir al usuario");
        }

    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function destroy($id) {
        $user = auth()->user();

        $follow = Follow::where('follower_id', $user->id)
            ->where('followed_id', $id)
            ->first();

        if (!$follow) {
            throw new ModelNotFoundException("No estás siguiendo a este usuario");
        }

        $follow->delete();

        return response()->json([
            'status' => true,
            'message' => 'Has dejado de seguir a este usuario'
        ], 200);
    }
}

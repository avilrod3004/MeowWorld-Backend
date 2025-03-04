<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller {

    /**
     * Lista todos los usuarios registrados
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse {
        $users = User::all();
        return response()->json([
            'status' => true,
            'data' => $users
        ], 200);
    }

    /**
     * Devuelve los datos de un usuario identificado por su id
     *
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'errors' => 'Usuario no encontrado'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $user
        ], 200);
    }

    /**
     * Devuelve los datos del usuario logueado
     *
     * @return JsonResponse
     */
    public function profile(): JsonResponse {
        $user = Auth::user();

        return response()->json([
            'status' => true,
            'data' => $user
        ], 200);
    }

    public function uploadimage(Request $request) {
        // Validar que se ha enviado un archivo
        $request->validate([
            'img_profile' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('img_profile')) {
            $result = Cloudinary::upload($request->file('img_profile')->getRealPath());

            $url = $result->getSecurePath();
            $public_id = $result->getPublicId();

            return response()->json(['url' => $url, 'public_id' => $public_id]);
        }

        return response()->json(['error' => 'No se ha subido ninguna imagen'], 400);
    }




    /**
     * Actualiza los datos de un usuario identificado por su id
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'errors' => 'Usuario no encontrado'
            ], 404);
        }

        $user->update($request->all());
        return response()->json([
            'status' => true,
            'message' => 'Usuario actualizado correctamente',
            'data' => $user
        ], 200);
    }

    /**
     * Elimina un usuario identificado por su id
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'errors' => 'Usuario no encontrado'
            ], 404);
        }

        $user->delete();
        return response()->json([
            'status' => true,
            'message' => 'Usuario eliminado correctamente',
        ], 200);
    }
}

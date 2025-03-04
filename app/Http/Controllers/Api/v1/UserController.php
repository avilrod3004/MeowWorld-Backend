<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

    /**
     * Actualiza los datos del usuario logueado, incluyendo la imagen de perfil
     */
    public function updateProfile(Request $request, $id): JsonResponse {
        $user = Auth::user();

        if ($user->id != $id) {
            return response()->json([
                'status' => false,
                'errors' => 'No tienes permisos para realizar cambios'
            ], 401);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|required|string|max:80|unique:users,username,' . $user->id,
            'description' => 'nullable|string',
            'img_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('img_profile')) {
            $result = Cloudinary::upload($request->file('img_profile')->getRealPath());
            $user->img_profile = $result->getSecurePath();
        }

        // Actualizar solo los campos válidos
        $user->fill($request->only(['name', 'username', 'description']));

        if (!$user->isDirty() && !$request->hasFile('img_profile')) {
            return response()->json([
                'status' => false,
                'message' => 'No se realizaron cambios en el perfil'
            ], 400);
        }

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Perfil actualizado correctamente',
            'data' => $user
        ], 200);
    }

    /**
     * Actualiza el email o contraseña del usuario logueado
     */
    public function updateCredentials(Request $request, $id): JsonResponse {
        $user = Auth::user();

        if ($user->id != $id) {
            return response()->json([
                'status' => false,
                'errors' => 'No tienes permisos para realizar cambios'
            ], 401);
        }

        // Validar los campos recibidos
        $request->validate([
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:8|confirmed',  // confirmación de contraseña
        ]);

        // Si el campo password está presente y es válido, actualizar la contraseña
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);  // Hash de la nueva contraseña
        }

        // Solo actualizar los campos necesarios (email o password)
        $user->fill($request->only(['email']));

        // Verificar si hay cambios, si no, devuelve un mensaje
        if (!$user->isDirty()) {
            return response()->json([
                'status' => false,
                'message' => 'No se realizaron cambios en las credenciales'
            ], 400);
        }

        // Guardar los cambios
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Credenciales actualizadas correctamente',
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

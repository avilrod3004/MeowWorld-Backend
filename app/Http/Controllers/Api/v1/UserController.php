<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\FilterUserRequest;
use App\Http\Requests\v1\UpdateCredentialsRequest;
use App\Http\Requests\v1\UpdateProfileRequest;
use App\Http\Resources\v1\UserResource;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserController extends Controller {

    /**
     * Lista todos los usuarios registrados
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse {
        $users = User::orderBy('created_at', 'desc')->paginate(12);

        return response()->json([
            'status' => true,
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'from' => $users->firstItem(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
            'links' => [
                'first' => $users->url(1),
                'last' => $users->url($users->lastPage()),
                'prev' => $users->previousPageUrl(),
                'next' => $users->nextPageUrl(),
            ]
        ], 200);
    }
    public function filterUsername(FilterUserRequest $request): JsonResponse {
        $query = $request->input('query');

        // Filtrar usuarios cuyo nombre contenga la palabra clave (case insensitive)
        $users = User::where('name', 'like', '%' . $query . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json([
            'status' => true,
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'from' => $users->firstItem(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
            'links' => [
                'first' => $users->url(1),
                'last' => $users->url($users->lastPage()),
                'prev' => $users->previousPageUrl(),
                'next' => $users->nextPageUrl(),
            ]
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
            throw new ModelNotFoundException("Usuario no encontrado");
        }

        return response()->json([
            'status' => true,
            'data' => new UserResource($user)
        ], 200);
    }

    /**
     * Actualiza los datos del usuario logueado, incluyendo la imagen de perfil
     */
    public function updateProfile(UpdateProfileRequest $request, $id): JsonResponse {
        $user = Auth::user();

        if ($user->id != $id) {
            throw new AuthorizationException();
        }

        // Obtener datos validados
        $validatedData = $request->validated();

        if ($request->hasFile('img_profile')) {
            $result = Cloudinary::upload($request->file('img_profile')->getRealPath());

            if (!$result) {
                throw new HttpException("No se pudo cambiar la foto de perfil. Error al guardar la imagen.");
            }

            $user->img_profile = $result->getSecurePath();
        }

//        $user->fill($validatedData);

//        if (!$user->isDirty() && !$request->hasFile('img_profile')) {
//            throw new HttpException("No se realizaron cambios en el perfil");
//        }

        if ($user->save()) {
            return response()->json([
                'status' => true,
                'message' => 'Perfil actualizado correctamente',
            ], 200);
        } else {
            throw new HttpException("No se pudo actualizar el perfil");
        }
    }

    /**
     * Actualiza el email o contraseña del usuario logueado
     */
    public function updateCredentials(UpdateCredentialsRequest $request, $id): JsonResponse {
        $user = Auth::user();

        if ($user->id != $id) {
            throw new AuthorizationException();
        }

        // Obtener datos validados
        $validatedData = $request->validated();

        // Verificar si se quiere cambiar la contraseña
        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        // Actualizar email si se proporciona
        if (!empty($validatedData['email']) && $validatedData['email'] !== $user->email) {
            $user->email = $validatedData['email'];
        }

        // Verificar si hay cambios
        if (!$user->isDirty()) {
            throw new HttpException("No se realizaron cambios en las credenciales");
        }

        // Guardar cambios
        if ($user->save()) {
            return response()->json([
                'status' => true,
                'message' => 'Credenciales actualizadas correctamente',
            ], 200);
        } else {
            throw new HttpException("No se pudo actualizar las credenciales");
        }
    }


    /**
     * Elimina un usuario identificado por su id
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse {
        $user = User::find($id);

        if (!$user) {
            throw new ModelNotFoundException("Usuario no encontrado");
        }

        $res = $user->delete();

        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'Usuario eliminado correctamente',
            ], 200);
        } else {
            throw new HttpException("No se puedo eliminar el usuario");
        }
    }
}

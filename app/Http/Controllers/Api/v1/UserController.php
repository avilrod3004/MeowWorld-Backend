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

/**
 * @OA\Info(
 *     title="MeowWorld API",
 *     description="Documentación de la API para gestionar la red social.",
 *     version="1.0.0",
 * )
 */

class UserController extends Controller {

    /**
     * Lista todos los usuarios registrados
     *
     * @return JsonResponse
     */
    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     summary="Lista todos los usuarios registrados",
     *     operationId="getUsers",
     *     tags={"Usuarios"},
     *     responses={
     *         @OA\Response(
     *             response=200,
     *             description="Lista de usuarios",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/User")
     *                 ),
     *                 @OA\Property(
     *                     property="meta",
     *                     type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="from", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=3),
     *                     @OA\Property(property="per_page", type="integer", example=12),
     *                     @OA\Property(property="total", type="integer", example=35)
     *                 ),
     *                 @OA\Property(
     *                     property="links",
     *                     type="object",
     *                     @OA\Property(property="first", type="string", example="/api/v1/users?page=1"),
     *                     @OA\Property(property="last", type="string", example="/api/v1/users?page=3"),
     *                     @OA\Property(property="prev", type="string", example="/api/v1/users?page=2"),
     *                     @OA\Property(property="next", type="string", example="/api/v1/users?page=2")
     *                 )
     *             )
     *         ),
     *         @OA\Response(
     *             response=404,
     *             description="No se encontraron usuarios"
     *         )
     *     }
     * )
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

    /**
     * @OA\Get(
     *     path="/api/v1/users/filter",
     *     summary="Filtra usuarios por nombre de usuario",
     *     operationId="filterUsername",
     *     tags={"Usuarios"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="Texto para buscar en los nombres de usuario",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     responses={
     *         @OA\Response(
     *             response=200,
     *             description="Usuarios encontrados",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/User")
     *                 ),
     *                 @OA\Property(property="meta", type="object", @OA\Property(property="total", type="integer", example=5))
     *             )
     *         ),
     *         @OA\Response(
     *             response=404,
     *             description="No se encontraron usuarios"
     *         )
     *     }
     * )
     */
    public function filterUsername(FilterUserRequest $request): JsonResponse {
        $query = $request->input('query');

        // Filtrar usuarios cuyo nombre contenga la palabra clave (case insensitive)
        $users = User::where('name', 'like', '%' . $query . '%')
            ->orderBy('created_at', 'desc')
            ->get();
        $totalResults = $users->count();

        return response()->json([
            'status' => true,
            'data' => UserResource::collection($users),
            'meta' => [
                'total' => $totalResults,
            ],
        ], 200);
    }


    /**
     * Devuelve los datos de un usuario identificado por su id
     *
     * @param $id
     * @return JsonResponse
     */
    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     summary="Devuelve los datos de un usuario identificado por su ID",
     *     operationId="getUserById",
     *     tags={"Usuarios"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     responses={
     *         @OA\Response(
     *             response=200,
     *             description="Usuario encontrado",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(property="data", ref="#/components/schemas/User")
     *             )
     *         ),
     *         @OA\Response(
     *             response=404,
     *             description="Usuario no encontrado"
     *         )
     *     }
     * )
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
    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}/profile",
     *     summary="Actualiza los datos del perfil del usuario",
     *     operationId="updateProfile",
     *     tags={"Usuarios"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="img_profile", type="string", format="uri")
     *         )
     *     ),
     *     responses={
     *         @OA\Response(
     *             response=200,
     *             description="Perfil actualizado correctamente",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="Perfil actualizado correctamente")
     *             )
     *         ),
     *         @OA\Response(
     *             response=403,
     *             description="No autorizado"
     *         ),
     *         @OA\Response(
     *             response=500,
     *             description="Error al actualizar perfil"
     *         )
     *     }
     * )
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
                throw new HttpException(500,"No se pudo cambiar la foto de perfil. Error al guardar la imagen.");
            }

            $user->img_profile = $result->getSecurePath();
        }

        $user->fill($validatedData);

//        if (!$user->isDirty() && !$request->hasFile('img_profile')) {
//            throw new HttpException("No se realizaron cambios en el perfil");
//        }

        if ($user->save()) {
            return response()->json([
                'status' => true,
                'message' => 'Perfil actualizado correctamente',
            ], 200);
        } else {
            throw new HttpException(500,"No se pudo actualizar el perfil");
        }
    }

    /**
     * Actualiza el email o contraseña del usuario logueado
     */
    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}/credentials",
     *     summary="Actualiza el email o la contraseña del usuario",
     *     operationId="updateCredentials",
     *     tags={"Usuarios"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password")
     *         )
     *     ),
     *     responses={
     *         @OA\Response(
     *             response=200,
     *             description="Credenciales actualizadas correctamente",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="Credenciales actualizadas correctamente")
     *             )
     *         ),
     *         @OA\Response(
     *             response=403,
     *             description="No autorizado"
     *         ),
     *         @OA\Response(
     *             response=500,
     *             description="Error al actualizar credenciales"
     *         )
     *     }
     * )
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
            throw new HttpException(500,"Credenciales ya en uso");
        }

        // Guardar cambios
        if ($user->save()) {
            return response()->json([
                'status' => true,
                'message' => 'Credenciales actualizadas correctamente',
            ], 200);
        } else {
            throw new HttpException(500,"No se pudo actualizar las credenciales");
        }
    }


    /**
     * Elimina un usuario identificado por su id
     * @param $id
     * @return JsonResponse
     */
    /**
     * @OA\Delete(
     *     path="/api/v1/users/{id}",
     *     summary="Elimina un usuario identificado por su ID",
     *     operationId="deleteUser",
     *     tags={"Usuarios"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     responses={
     *         @OA\Response(
     *             response=200,
     *             description="Usuario eliminado correctamente",
     *             @OA\JsonContent(
     *                 type="object",
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="Usuario eliminado correctamente")
     *             )
     *         ),
     *         @OA\Response(
     *             response=404,
     *             description="Usuario no encontrado"
     *         ),
     *         @OA\Response(
     *             response=500,
     *             description="Error al eliminar usuario"
     *         )
     *     }
     * )
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
            throw new HttpException(500,"No se puedo eliminar el usuario");
        }
    }
}

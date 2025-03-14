<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\LoginRequest;
use App\Http\Requests\v1\RegisterRequest;
use App\Http\Resources\v1\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller {
    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     summary="Registrar un nuevo usuario",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "username", "email", "password"},
     *             @OA\Property(property="name", type="string", example="Juan Pérez"),
     *             @OA\Property(property="username", type="string", example="juanperez"),
     *             @OA\Property(property="email", type="string", format="email", example="juanperez@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secreta123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario registrado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="token_generado"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usuario registrado correctamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Datos inválidos"),
     *     @OA\Response(response=500, description="Error al registrar el usuario")
     * )
     */
    public function register(RegisterRequest $request): JsonResponse {
        // Crear el usuario
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Generar token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Asignar el rol 'user'
        $userRole = Role::where('name', 'user')->first();

        if ($userRole) {
            $user->roles()->attach($userRole);
        } else {
            throw new HttpException('Rol "user" no encontrado. Contacta con un administrador.');
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'status' => true,
            'message' => 'Usuario registrado correctamente',
            'data' => new UserResource($user),
        ], 201);
    }


    /**
     * @throws AuthenticationException
     */
    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Iniciar sesión de usuario",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="juanperez@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secreta123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sesión iniciada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="token_generado"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sesión iniciada correctamente"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Credenciales inválidas")
     * )
     */
    public function login(LoginRequest $request): JsonResponse {
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw new AuthenticationException("Crendiales invalidas");
        }

        $user = Auth::user();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'status' => true,
            'message' => 'Sesión iniciada correctamente',
            'data' => new UserResource($user),
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/me",
     *     summary="Obtener datos del usuario autenticado",
     *     tags={"Autenticación"},
     *     security={{ "bearer": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Datos del usuario autenticado",
     *         @OA\JsonContent(
     *             type="object",
     *             ref="#/components/schemas/User"
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function me(): JsonResponse {
        $user = Auth::user();

        return response()->json([
            'status' => true,
            'data' => new UserResource($user)
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/refresh",
     *     summary="Refrescar el token de acceso",
     *     tags={"Autenticación"},
     *     security={{ "bearer": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Token refrescado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="nuevo_token_generado"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token refrescado correctamente")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function refresh(): JsonResponse {
        $user = Auth::user();

        // Revoca el token anterior
        auth()->user()->tokens()->delete();

        // Genera un nuevo token
        $newToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'access_token' => $newToken,
            'token_type' => 'Bearer',
            'message' => 'Token refrescado correctamente'
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/logout",
     *     summary="Cerrar sesión del usuario",
     *     tags={"Autenticación"},
     *     security={{ "bearer": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Sesión cerrada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sesión cerrada correctamente")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function logout(): JsonResponse {
        // Elimina todos los tokens que haya generado el usuario
        auth()->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Sesion cerrada correctamente',
        ], 200);
    }
}

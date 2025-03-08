<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\LoginRequest;
use App\Http\Requests\v1\RegisterRequest;
use App\Http\Resources\v1\UserResource;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller {
    public function register(RegisterRequest $request): JsonResponse {
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'status' => true,
            'message' => 'Usuario registrado correctamente'
        ], 200);
    }

    /**
     * @throws AuthenticationException
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
        ], 200);
    }

    public function me(): JsonResponse {
        $user = Auth::user();

        return response()->json([
            'status' => true,
            'data' => new UserResource($user)
        ], 200);
    }

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

    public function logout(): JsonResponse {
        // Elimina todos los tokens que haya generado el usuario
        auth()->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Sesion cerrada correctamente',
        ], 200);
    }
}

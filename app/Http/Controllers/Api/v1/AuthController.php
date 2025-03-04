<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller {
    public function register(Request $request): \Illuminate\Http\JsonResponse {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:80|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ];

        // crear validación
        $validator = \Validator::make($request->input(), $rules);

        // en caso de no cumplir las reglas
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }

        // crear el usuario
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // nombre del token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'status' => true,
            'message' => 'Usuario creado correctamente'
        ], 200);
    }

    public function login(Request $request): \Illuminate\Http\JsonResponse {
        $rules = [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
        ];

        $validator = \Validator::make($request->input(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }

        // si no coincide con un registro de la base de datos
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => false,
                'errors' => 'Credenciales invalidas'
            ], 401);
        }

        // buscar la infor del usuario en la base de datos
        // $user = User::where('email', $request->email)->first();

        $user = Auth::user();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'status' => true,
            'message' => 'Login correcto',
            'data' => $user
        ], 200);
    }

    public function me(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'status' => true,
            'data' => $user
        ], 200);
    }

    public function refresh(): \Illuminate\Http\JsonResponse {
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

    public function logout(): \Illuminate\Http\JsonResponse {
        // Elimina todos los tokens que haya generado el usuario
        auth()->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Sesion cerrada correctamente',
        ], 200);
    }
}

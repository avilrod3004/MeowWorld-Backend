<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\PostController;
use App\Http\Controllers\Api\v1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::get('/', function () {
    return "Hello world";
});

Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

// Rutas protegidas, accesibles por token
Route::middleware('auth:sanctum')->group(function () {
    // Controlador de autenticacion
    Route::get('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);

    // Controlador de usuarios
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/profile', [UserController::class, 'profile']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::post('users/{id}/profile', [UserController::class, 'updateProfile']);
    Route::post('users/{id}/credentials', [UserController::class, 'updateCredentials']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);

    // Controlador de posts
//    Route::group(['prefix' => 'v1/posts'], function () {})  ???

    Route::get('v1/posts', [PostController::class, 'index']);
    Route::get('v1/posts/user/{id}', [PostController::class, 'getUserPosts']);
    Route::get('v1/posts/{id}', [PostController::class, 'show']);
    Route::post('v1/posts', [PostController::class, 'store']);
    Route::put('v1/posts/{id}', [PostController::class, 'update']);
    Route::delete('v1/posts/{id}', [PostController::class, 'destroy']);
});

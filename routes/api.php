<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\CatController;
use App\Http\Controllers\Api\v1\ComentarioController;
use App\Http\Controllers\Api\v1\FollowController;
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

Route::post('v1/auth/register', [AuthController::class, 'register']);
Route::post('v1/auth/login', [AuthController::class, 'login']);

// Rutas protegidas, accesibles por token
Route::middleware('auth:sanctum')->group(function () {

    // Rutas protegidas por rol de administrador
    Route::middleware('role:admin')->group(function () {
        // Controlador de usuarios
        Route::get('v1/users', [UserController::class, 'index']);
        Route::delete('v1/users/{id}', [UserController::class, 'destroy']);

        // Controlador de gatos
        Route::get('v1/cats', [CatController::class, 'index']);

        // Controlador de comentarios
        Route::get('v1/comments', [ComentarioController::class, 'index']);
    });

    // Controlador de autenticacion
    Route::get('v1/auth/me', [AuthController::class, 'me']);
    Route::get('v1/auth/logout', [AuthController::class, 'logout']);
    Route::post('v1/auth/refresh', [AuthController::class, 'refresh']);

    // Controlador de usuarios
    Route::get('v1/users/filter', [UserController::class, 'filterUsername']);
    Route::get('v1/users/{id}', [UserController::class, 'show']);
    Route::post('v1/users/{id}/profile', [UserController::class, 'updateProfile']);
    Route::post('v1/users/{id}/credentials', [UserController::class, 'updateCredentials']);

    // Controlador de posts
    Route::get('v1/posts', [PostController::class, 'index']);
    Route::get('v1/posts/user/{id}', [PostController::class, 'getUserPosts']);
    Route::get('v1/posts/{id}', [PostController::class, 'show']);
    Route::post('v1/posts', [PostController::class, 'store']);
    Route::put('v1/posts/{id}', [PostController::class, 'update']);
    Route::delete('v1/posts/{id}', [PostController::class, 'destroy']);

    // Controlador de gatos
    Route::get('v1/cats/filter', [CatController::class, 'filterUsername']);
    Route::get('v1/cats/user/{id}', [CatController::class, 'getUserCats']);
    Route::get('v1/cats/{id}', [CatController::class, 'show']);
    Route::post('v1/cats', [CatController::class, 'store']);
    Route::post('v1/cats/{id}', [CatController::class, 'update']);
    Route::delete('v1/cats/{id}', [CatController::class, 'destroy']);

    // Controlador de comentarios
    Route::get('v1/comments/post/{id}', [ComentarioController::class, 'getPostComentarios']);
    Route::get('v1/comments/user/{id}', [ComentarioController::class, 'getUserComentarios']);
    Route::get('v1/comments/{id}', [ComentarioController::class, 'show']);
    Route::post('v1/comments', [ComentarioController::class, 'store']);
    Route::delete('v1/comments/{id}', [ComentarioController::class, 'destroy']);

    // Controlar de seguidos
    Route::get('v1/follows/followers/{id}', [FollowController::class, 'getFollowers']);
    Route::get('v1/follows/following/{id}', [FollowController::class, 'getFollowing']);
    Route::get('v1/follows/isfollowing/{id}', [FollowController::class, 'isFollowing']);
    Route::get('v1/follows/isfollowed/{id}', [FollowController::class, 'isFollowed']);
    Route::post('v1/follows', [FollowController::class, 'store']);
    Route::delete('v1/follows/unfollow/{id}', [FollowController::class, 'destroy']);
});

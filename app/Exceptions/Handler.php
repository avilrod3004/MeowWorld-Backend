<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): Response|JsonResponse|\Symfony\Component\HttpFoundation\Response|RedirectResponse {
        // No hay un usuario autenticado
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'status' => false,
                'message' => 'No autenticado. Por favor, inicia sesión para continuar.',
            ], 401);
        }

        // Recursos no encontrados
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 404);
        }

        // Los datos no cumplen las validaciones
        if ($e instanceof ValidationException) {
            return response()->json([
                'status' => false,
                'error' => $e->validator->errors(),
            ], 422);
        }

        // El usuario no tiene permisos
        if ($e instanceof AuthorizationException) {
            return response()->json([
                'status' => false,
                'error' => 'No tienes permisos para realizar esta acción',
            ], 403);
        }

        // Acción que no se ha podido completar
        if ($e instanceof HttpException) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }

        return parent::render($request, $e);
    }
}

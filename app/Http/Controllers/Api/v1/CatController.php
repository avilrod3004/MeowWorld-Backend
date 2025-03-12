<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\CatRequest;
use App\Http\Requests\v1\FilterCatRequest;
use App\Http\Requests\v1\UpdateCatRequest;
use App\Http\Resources\v1\CatResource;
use App\Models\Cat;
use App\Models\User;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use function PHPUnit\Framework\isEmpty;

class CatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse {
        $cats = Cat::paginate(10);

        return response()->json([
            'status' => true,
            'data' => CatResource::collection($cats),
            'meta' => [
                'current_page' => $cats->currentPage(),
                'from' => $cats->firstItem(),
                'last_page' => $cats->lastPage(),
                'per_page' => $cats->perPage(),
                'total' => $cats->total(),
            ],
            'links' => [
                'first' => $cats->url(1),
                'last' => $cats->url($cats->lastPage()),
                'prev' => $cats->previousPageUrl(),
                'next' => $cats->nextPageUrl(),
            ]
        ], 200);
    }

    public function filterUsername(FilterCatRequest $request): JsonResponse {
        $query = $request->input('query');

        // Filtrar usuarios cuyo nombre contenga la palabra clave (case insensitive)
        $cats = Cat::where('name', 'like', '%' . $query . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json([
            'status' => true,
            'data' => CatResource::collection($cats),
            'meta' => [
                'current_page' => $cats->currentPage(),
                'from' => $cats->firstItem(),
                'last_page' => $cats->lastPage(),
                'per_page' => $cats->perPage(),
                'total' => $cats->total(),
            ],
            'links' => [
                'first' => $cats->url(1),
                'last' => $cats->url($cats->lastPage()),
                'prev' => $cats->previousPageUrl(),
                'next' => $cats->nextPageUrl(),
            ]
        ], 200);
    }

    public function getUserCats($userId): JsonResponse {
        $user = User::find($userId);

        if (!$user) {
            throw new ModelNotFoundException("No existe el usuario");
        }

        $cats = Cat::where('user_id', $userId)->paginate(12);

        return response()->json([
            'status' => true,
            'data' => CatResource::collection($cats),
            'meta' => [
                'current_page' => $cats->currentPage(),
                'from' => $cats->firstItem(),
                'last_page' => $cats->lastPage(),
                'per_page' => $cats->perPage(),
                'total' => $cats->total(),
            ],
            'links' => [
                'first' => $cats->url(1),
                'last' => $cats->url($cats->lastPage()),
                'prev' => $cats->previousPageUrl(),
                'next' => $cats->nextPageUrl(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CatRequest $request) {
        $user = Auth::user();

        $cat = new Cat();
        $cat->name = $request->input('name');
        $cat->description = $request->input('description');
        $cat->en_adopcion = filter_var($request->input('en_adopcion'), FILTER_VALIDATE_BOOLEAN);;
        $cat->user()->associate($user);

        if ($request->hasFile('image')) {
            $result = Cloudinary::upload($request->file('image')->getRealPath());

            if (!$result) {
                throw new HttpException("No se pudo registrar el gato. Error al guardar la imagen.");
            }

            $cat->image = $result->getSecurePath();
        }

        $res = $cat->save();

        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'Gato registrado correctamente',
                'data' => new CatResource($cat),
            ], 201);
        } else {
            throw new HttpException("No se pudo registrar el gato");
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id) {
        $cat = Cat::find($id);

        if (!$cat) {
            throw new ModelNotFoundException("Gato no encontrado");
        }

        return response()->json([
            'status' => true,
            'data' => new CatResource($cat),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     * @throws AuthorizationException
     */
    public function update(UpdateCatRequest $request, $id) {
        $cat = Cat::find($id);

        if (!$cat) {
            throw new ModelNotFoundException("Gato no encontrado");
        }

        if (Auth::user()->id !== $cat->user_id) {
            throw new AuthorizationException();
        }

        if (!empty($request->input('name'))) {
            $cat->name = $request->input('name');
        }

        if (!empty($request->input('description'))) {
            $cat->description = $request->input('description');
        }

        if (!empty($request->input('en_adopcion'))) {
            $isActive = filter_var($request->input('en_adopcion'), FILTER_VALIDATE_BOOLEAN);
            $cat->en_adopcion = $isActive;

        }

        if ($request->hasFile('image')) {
            $result = Cloudinary::upload($request->file('image')->getRealPath());

            if (!$result) {
                throw new HttpException("No se pudo actualizar la información del gato. Error al guardar la imagen.");
            }

            $cat->image = $result->getSecurePath();
        }

        $res = $cat->save();

        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'Información actualizada correctamente',
                'data' => new CatResource($cat),
            ], 200);
        } else {
            throw new HttpException("No se pudo actualizar la información del gato");
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {
        $cat = Cat::find($id);

        if (!$cat) {
            throw new ModelNotFoundException("Gato no encontrado");
        }

        if (Auth::user()->id !== $cat->user_id && !Auth::user()->hasRole("admin")) {
            throw new AuthorizationException();
        }

        $res = $cat->delete();

        if ($res) {
            return response()->json([
                'status' => true,
                'message' => 'Gato eliminado correctamente',
            ], 200);
        } else {
            throw new HttpException("No se pudo eliminar el gato");
        }
    }
}

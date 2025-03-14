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
    /**
     * @OA\Get(
     *     path="/api/v1/cats",
     *     summary="Obtener lista de gatos",
     *     tags={"Gatos"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de gatos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Cat")
     *             )
     *         )
     *     )
     * )
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

    /**
     * @OA\Post(
     *     path="/api/v1/cats/filter",
     *     summary="Filtrar gatos por nombre",
     *     tags={"Gatos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="query", type="string", example="Fluffy")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de gatos filtrados",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Cat")
     *             )
     *         )
     *     )
     * )
     */
    public function filterUsername(FilterCatRequest $request): JsonResponse {
        $query = $request->input('query');

        // Filtrar gatos cuyo nombre contenga la palabra clave (case insensitive)
        $cats = Cat::where('name', 'like', '%' . $query . '%')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalResults = $cats->count();

        return response()->json([
            'status' => true,
            'data' => CatResource::collection($cats),
            'meta' => [
                'total' => $totalResults,
            ],
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{userId}/cats",
     *     summary="Obtener gatos de un usuario",
     *     tags={"Gatos"},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Gatos del usuario",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Cat")
     *             )
     *         )
     *     )
     * )
     */
    public function getUserCats($userId): JsonResponse {
        $user = User::find($userId);

        if (!$user) {
            throw new ModelNotFoundException("No existe el usuario");
        }

        $cats = Cat::where('user_id', $userId)->get();
        $totalResults = $cats->count();

        return response()->json([
            'status' => true,
            'data' => CatResource::collection($cats),
            'meta' => [
                'total' => $totalResults,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/v1/cats",
     *     summary="Crear un nuevo gato",
     *     tags={"Gatos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Cat")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Gato creado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Cat"
     *             )
     *         )
     *     )
     * )
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
                throw new HttpException(500,"No se pudo registrar el gato. Error al guardar la imagen.");
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
            throw new HttpException(500,"No se pudo registrar el gato");
        }
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     path="/api/v1/cats/{id}",
     *     summary="Mostrar un gato",
     *     tags={"Gatos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del gato",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Gato encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Cat"
     *             )
     *         )
     *     )
     * )
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
    /**
     * @OA\Put(
     *     path="/api/v1/cats/{id}",
     *     summary="Actualizar un gato",
     *     tags={"Gatos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del gato",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Cat")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Gato actualizado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Cat"
     *             )
     *         )
     *     )
     * )
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
                throw new HttpException(500,"No se pudo actualizar la información del gato. Error al guardar la imagen.");
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
            throw new HttpException(500,"No se pudo actualizar la información del gato");
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/v1/cats/{id}",
     *     summary="Eliminar un gato",
     *     tags={"Gatos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del gato",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Gato eliminado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Gato eliminado correctamente"
     *             )
     *         )
     *     )
     * )
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
            throw new HttpException(500, "No se pudo eliminar el gato");
        }
    }
}

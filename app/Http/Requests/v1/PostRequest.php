<?php

namespace App\Http\Requests\v1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="PostRequest",
 *     type="object",
 *     required={"image", "description"},
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         format="uri",
 *         description="Imagen del post. Se requiere que sea de tipo jpeg, png o jpg y no exceda los 2MB.",
 *         example="https://cloudinary.com/ejemplo-imagen.jpg"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Descripción del post. Campo obligatorio y con un máximo de 2000 caracteres.",
 *         example="Este es un post de ejemplo para la documentación Swagger"
 *     )
 * )
 */

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'required|max:2000',
        ];
    }

    public function messages(): array {
        return [
            'image.required' => 'La imagen es obligatoria.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe ser un archivo de tipo jpeg, png o jpg.',
            'image.max' => 'La imagen no debe ser mayor a 2MB.',
            'description.required' => 'La descripción es obligatoria.',
            'description.max' => 'La descripción no puede superar los 2000 caracteres.',
        ];
    }
}

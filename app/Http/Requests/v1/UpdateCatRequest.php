<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCatRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'name' => 'sometimes|string|max:80',
            'description' => 'sometimes|string|max:2000',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'en_adopcion' => 'sometimes|string|in:true,false',
        ];
    }

    public function messages(): array {
        return [
            'name.max' => 'El campo nombre no puede superar los 80 caracteres',

            'description.max' => 'El campo descripcion no puede superar los 2000 caracteres',

            'image.image' => 'El archivo debe ser una imagen',
            'image.mimes' => 'La imagen debe ser un archivo de tipo jpeg, png o jpg.',
            'image.max' => 'La imagen no debe ser mayor a 2MB.',

            'en_adopcion.in' => 'El campo en_adopcion solo puede ser "true" o "false".',
        ];
    }

}

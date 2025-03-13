<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:80',
            'description' => 'required|string|max:2000',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'en_adopcion' => 'required|string|in:true,false',
        ];
    }

    public function messages(): array {
        return [
            'name.required' => 'El campo nombre es obligatorio',
            'name.max' => 'El campo nombre no puede superar los 80 caracteres',

            'description.required' => 'El campo descripcion es obligatorio',
            'description.max' => 'El campo descripcion no puede superar los 2000 caracteres',

            'image.required' => 'El campo imagen es obligatorio',
            'image.image' => 'El archivo debe ser una imagen',
            'image.mimes' => 'La imagen debe ser un archivo de tipo jpeg, png o jpg.',
            'image.max' => 'La imagen no debe ser mayor a 2MB.',

            'en_adopcion.required' => 'El campo en adopción es obligatorio',
            'en_adopcion.in' => 'El campo en_adopcion solo puede ser "true" o "false".',
        ];
    }
}

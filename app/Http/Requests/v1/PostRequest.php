<?php

namespace App\Http\Requests\v1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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

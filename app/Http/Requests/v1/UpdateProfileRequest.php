<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
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
        $user = Auth::user();

        return [
            'name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:80|unique:users,username,' . $user->id,
            'description' => 'sometimes|string|max:2000',
            'img_profile' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function messages(): array {
        return [
            'name.max' => 'El nombre no puede exceder los 255 caracteres.',

            'username.unique' => 'El nombre de usuario ya existe.',
            'username.max' => 'El nombre de usuario no puede exceder los 80 caracteres.',

            'description.max' => 'La descripcion no puede exceder los 2000 caracteres.',

            'img_profile.image' => 'El archivo debe ser una imagen.',
            'img_profile.mimes' => 'El archivo debe ser una imagen con formato: jpeg, png, jpg.',
            'img_profile.max' => 'El archivo debe ser una imagen con formato: 2000 kb.',
        ];
    }
}

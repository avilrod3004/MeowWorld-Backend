<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ComentarioRequest extends FormRequest
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
            'text' => 'required|string|max:255',
            'post_id' => 'required|integer|exists:posts,id',
        ];
    }

    public function messages(): array {
        return [
            'text.required' => 'El campo texto es obligatorio.',
            'text.string' => 'El campo debe ser un texto.',
            'text.max' => 'El campo texto no puede superar los 255 caracteres.',

            'post_id.required' => 'El campo post_id es obligatorio.',
            'post_id.integer' => 'El campo post_id debe ser un entero.',
            'post_id.exists' => 'El post no existe.',
        ];
    }
}

<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CatPostRequest extends FormRequest
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
            'cat_id' => 'required|integer|exists:cats,id',
            'post_id' => 'required|integer|exists:posts,id',
        ];
    }

    public function messages(): array {
        return [
            'cat_id.required' => 'El campo cat_id es obligatorio.',
            'cat_id.integer' => 'El campo cat_id debe ser un entero.',
            'cat_id.exists' => 'No existe un cat con ese ID.',

            'post_id.required' => 'El campo post_id es obligatorio.',
            'post_id.integer' => 'El campo post_id debe ser un entero.',
            'post_id.exists' => 'No existe un post con ese ID.',
        ];
    }
}

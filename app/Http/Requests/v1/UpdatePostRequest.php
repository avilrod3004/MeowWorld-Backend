<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePostRequest extends FormRequest
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
    public function rules(): array {
        return [
            'description' => 'required|max:2000',
        ];
    }

    public function messages(): array {
        return [
            'description.required' => 'La descripción es obligatoria.',
            'description.max' => 'La descripción no puede superar los 2000 caracteres.',
        ];
    }
}

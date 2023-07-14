<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max: 255',
            'content' => 'string',
            'type' => 'string',
            'category' => 'required|array',
        ];
    }
    public function messages()
    {
        return [
            'title.required' => 'name is required!',
            'content.required' => 'type is not string!',
        ];
    }
}

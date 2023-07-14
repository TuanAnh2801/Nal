<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class  CategoryRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'name' => 'required',
            'image' => 'image|mimes:png,jpg,svg|max:10240',
            'type' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'name is required!',
            'type.required' => 'type is required!',
            'image.image' => 'image is not image',
            'image.mimes' => 'the picture is not in the correct format'
        ];
    }


}

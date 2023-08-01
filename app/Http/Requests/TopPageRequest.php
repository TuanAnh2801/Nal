<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TopPageRequest extends FormRequest
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
            'company_name'=>'required|string',
            'area'=>'required|string',
            'summary'=>'required|string|max:200',
            'about'=> 'string|max:1000',
            'intro_video'=> 'url',
            'link_website'=> 'required|url',
            'link_facebook'=>'url|starts_with:https://www.facebook.com/',
            'link_instagram'=> 'url|starts_with:https://www.instagram.com/',
            'status'=> 'in:active,inactive'
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Traits\HttpTrait;
use Illuminate\Foundation\Http\FormRequest;

class ImportCookiesRequest extends FormRequest
{
    use HttpTrait;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cookies' => 'required|string',
            'username' => 'required|string|exists:channels,username',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.exists' => 'The username does not exist',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChannelUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'proxy' => 'required',
            'status' => 'required|int',
            'user_id' => 'required|exists:users,id',
            'title' => 'max:255',
            'hash_tag_id' => 'required|int',
            'video_url' => 'nullable|url',
        ];
    }
}

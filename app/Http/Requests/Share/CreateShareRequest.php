<?php

namespace App\Http\Requests\Share;

use Illuminate\Foundation\Http\FormRequest;

class CreateShareRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:file,folder'],
            'id' => ['required', 'integer'],
            'permission' => ['nullable', 'string', 'in:view,download'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'recipient_email' => ['nullable', 'email', 'exists:users,email'],
        ];
    }
}

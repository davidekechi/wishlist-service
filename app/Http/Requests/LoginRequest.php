<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'email.required'    => 'Email address is required',
            'email.email'       => 'Please provide a valid email address',
            'password.required' => 'Password is required',
        ];
    }
}

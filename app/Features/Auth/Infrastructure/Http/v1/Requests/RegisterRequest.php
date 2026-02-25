<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Http\v1\Requests;

use App\Features\Auth\Infrastructure\Http\v1\Rules\PasswordRuleAdapter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\ValidationRule;

class RegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => ['required', 'string', new PasswordRuleAdapter()],
        ];
    }

    public function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'error' => [
                'code'    => 'VALIDATION_ERROR',
                'message' => 'The given data was invalid.',
                'details' => $validator->errors(),
            ],
        ], 422));
    }
}
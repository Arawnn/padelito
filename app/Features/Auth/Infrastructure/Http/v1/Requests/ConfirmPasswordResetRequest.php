<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Http\v1\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ConfirmPasswordResetRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed'],
        ];
    }

    public function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'The given data was invalid.',
                'details' => $validator->errors(),
            ],
        ], 422));
    }
}

<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RespondToMatchInvitationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'accept' => ['required', 'boolean'],
        ];
    }

    public function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'The given data was invalid.', 'details' => $validator->errors()],
        ], 422));
    }
}

<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ChangeUsernameRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => $this->has('username')
                ? strip_tags((string) $this->input('username'))
                : null,
        ]);
    }

    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-z0-9_]+$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.regex' => 'The username may only contain lowercase letters, digits and underscores.',
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

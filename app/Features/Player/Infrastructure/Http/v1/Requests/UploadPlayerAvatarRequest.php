<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadPlayerAvatarRequest extends FormRequest
{
    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'avatar' => ['nullable'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->sometimes(
            'avatar',
            ['file', 'mimes:jpg,jpeg,png', 'max:2048'],
            fn () => $this->hasFile('avatar'),
        );

        $validator->sometimes(
            'avatar',
            ['string', 'max:2048', 'regex:/^https:\/\/.+/i'],
            fn () => ! $this->hasFile('avatar') && $this->filled('avatar'),
        );
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'avatar.mimes' => 'Avatar must be a JPG or PNG file.',
            'avatar.max' => 'Avatar must not exceed 2 MB.',
            'avatar.regex' => 'Avatar must be an HTTPS URL when sent as text.',
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

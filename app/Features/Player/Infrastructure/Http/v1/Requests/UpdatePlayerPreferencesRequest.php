<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Requests;

use App\Features\Player\Domain\Enums\DominantHandEnum;
use App\Features\Player\Domain\Enums\PreferredPositionEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePlayerPreferencesRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'location' => $this->has('location') ? ($this->input('location') !== null ? strip_tags((string) $this->input('location')) : null) : null,
        ]);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'dominantHand' => ['nullable', Rule::in(array_column(DominantHandEnum::cases(), 'value'))],
            'preferredPosition' => ['nullable', Rule::in(array_column(PreferredPositionEnum::cases(), 'value'))],
            'location' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'dominantHand.in' => 'Invalid dominant hand. Accepted values: '.implode(', ', array_column(DominantHandEnum::cases(), 'value')).'.',
            'preferredPosition.in' => 'Invalid preferred position. Accepted values: '.implode(', ', array_column(PreferredPositionEnum::cases(), 'value')).'.',
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

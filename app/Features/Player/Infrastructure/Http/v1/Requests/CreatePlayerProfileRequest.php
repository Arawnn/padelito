<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Http\v1\Requests;

use App\Features\Player\Domain\Enums\DominantHandEnum;
use App\Features\Player\Domain\Enums\PlayerLevelEnum;
use App\Features\Player\Domain\Enums\PreferredPositionEnum;
use App\Features\Player\Infrastructure\Persistence\Eloquent\Models\Player;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreatePlayerProfileRequest extends FormRequest
{
    /**
     * Strip HTML tags from all text inputs to prevent injection.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => $this->has('username') ? strip_tags((string) $this->input('username')) : null,
            'displayName' => $this->has('displayName') ? strip_tags((string) $this->input('displayName')) : null,
            'bio' => $this->has('bio') ? strip_tags((string) $this->input('bio')) : null,
            'location' => $this->has('location') ? strip_tags((string) $this->input('location')) : null,
        ]);
    }

    /**
     * @return array<string, array<mixed>|string|ValidationRule>
     */
    public function rules(): array
    {
        $levelValues = array_column(PlayerLevelEnum::cases(), 'value');
        $dominantHandValues = array_column(DominantHandEnum::cases(), 'value');
        $preferredPositionValues = array_column(PreferredPositionEnum::cases(), 'value');

        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique(Player::class, 'username'),
            ],
            'displayName' => [
                'required',
                'string',
                'max:30',
                'regex:/^[\pL\s]+$/u',
            ],
            'bio' => [
                'nullable',
                'string',
                'max:120',
            ],
            'location' => [
                'nullable',
                'string',
                'max:255',
            ],
            'avatar' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],
            'level' => [
                'required',
                Rule::in($levelValues),
            ],
            'dominantHand' => [
                'nullable',
                Rule::in($dominantHandValues),
            ],
            'preferredPosition' => [
                'nullable',
                Rule::in($preferredPositionValues),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.regex' => 'The username may only contain lowercase letters, digits and underscores.',
            'username.unique' => 'This username is already taken.',
            'displayName.regex' => 'The display name may only contain letters and spaces.',
            'level.in' => 'Invalid level. Accepted values: '.implode(', ', array_column(PlayerLevelEnum::cases(), 'value')).'.',
            'dominantHand.in' => 'Invalid dominant hand. Accepted values: '.implode(', ', array_column(DominantHandEnum::cases(), 'value')).'.',
            'preferredPosition.in' => 'Invalid preferred position. Accepted values: '.implode(', ', array_column(PreferredPositionEnum::cases(), 'value')).'.',
            'avatar.mimes' => 'Avatar must be a JPG or PNG file.',
            'avatar.max' => 'Avatar must not exceed 2 MB.',
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

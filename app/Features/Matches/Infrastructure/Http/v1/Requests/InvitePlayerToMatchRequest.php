<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Requests;

use App\Features\Matches\Domain\Enums\InvitationTypeEnum;
use App\Features\Matches\Domain\Enums\TeamEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class InvitePlayerToMatchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'invitee_id' => ['required', 'uuid'],
            'team' => ['required', Rule::in(array_column(TeamEnum::cases(), 'value'))],
            'type' => ['required', Rule::in(array_column(InvitationTypeEnum::cases(), 'value'))],
        ];
    }

    public function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'The given data was invalid.', 'details' => $validator->errors()],
        ], 422));
    }
}

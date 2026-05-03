<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Requests;

use App\Features\Matches\Domain\Enums\MatchFormatEnum;
use App\Features\Matches\Domain\Enums\MatchTypeEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateMatchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'match_type' => ['sometimes', Rule::in(array_column(MatchTypeEnum::cases(), 'value'))],
            'match_format' => ['sometimes', Rule::in(array_column(MatchFormatEnum::cases(), 'value'))],
            'court_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'match_date' => ['sometimes', 'nullable', 'date'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'sets_detail' => ['sometimes', 'nullable', 'array'],
            'sets_detail.*.a' => ['required_with:sets_detail', 'integer', 'min:0'],
            'sets_detail.*.b' => ['required_with:sets_detail', 'integer', 'min:0'],
            'sets_to_win' => ['sometimes', 'integer', 'min:1', 'max:3'],
        ];
    }

    public function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'The given data was invalid.', 'details' => $validator->errors()],
        ], 422));
    }
}

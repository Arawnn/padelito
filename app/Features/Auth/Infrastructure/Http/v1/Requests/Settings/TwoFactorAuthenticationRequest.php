<?php

namespace App\Features\Auth\Infrastructure\Http\v1\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TwoFactorAuthenticationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string|ValidationRule>
     */
    public function rules(): array
    {
        return [];
    }
}

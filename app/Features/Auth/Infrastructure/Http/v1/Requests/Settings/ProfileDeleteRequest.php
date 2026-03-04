<?php

namespace App\Features\Auth\Infrastructure\Http\v1\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileDeleteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string|ValidationRule>
     */
    public function rules(): array
    {
        return [
            'password' => $this->currentPasswordRules(),
        ];
    }
}

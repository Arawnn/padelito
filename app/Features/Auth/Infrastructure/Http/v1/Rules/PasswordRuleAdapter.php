<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Http\v1\Rules;

use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Features\Auth\Domain\ValueObjects\Password;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class PasswordRuleAdapter implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
       try {
        Password::fromPlainText($value);
       } catch (InvalidPasswordException $e) {
        foreach($e->violations() as $violation) {
            $fail($violation);
        }
       }
    }
}
    
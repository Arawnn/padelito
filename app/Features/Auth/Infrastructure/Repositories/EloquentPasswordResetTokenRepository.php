<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Repositories;

use App\Features\Auth\Domain\Repositories\PasswordResetTokenRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class EloquentPasswordResetTokenRepository implements PasswordResetTokenRepositoryInterface
{
    private const TABLE = 'password_reset_tokens';
    private const EXPIRY_MINUTES = 60;

    public function create(Email $email): string
    {
        $token = Str::random(64);

        DB::table(self::TABLE)->upsert(
            [
                'email' => $email->value(),
                'token' => Hash::make($token),
                'created_at' => now(),
            ],
            uniqueBy: ['email'],
            update: ['token', 'created_at'],
        );

        return $token;
    }

    public function isValid(Email $email, string $token): bool
    {
        $record = DB::table(self::TABLE)
            ->where('email', $email->value())
            ->first()
        ;

        if (!$record) {
            return false;
        }

        $isExpired = Carbon::parse($record->created_at)
            ->addMinutes(self::EXPIRY_MINUTES)
            ->isPast()
        ;

        if ($isExpired) {
            return false;
        }

        return Hash::check($token, $record->token);
    }

    public function delete(Email $email): void
    {
        DB::table(self::TABLE)
            ->where('email', $email->value())
            ->delete()
        ;
    }
}

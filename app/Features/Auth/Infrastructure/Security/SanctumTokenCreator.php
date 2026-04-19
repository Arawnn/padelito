<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Security;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Infrastructure\Contracts\TokenCreatorInterface;
use App\Features\Auth\Infrastructure\Persistence\Eloquent\Models\User as EloquentUser;

final readonly class SanctumTokenCreator implements TokenCreatorInterface
{
    public function createFor(User $user): string
    {
        /** @var EloquentUser $model */
        $model = EloquentUser::findOrFail($user->id()->value());

        return $model->createToken('auth_token')->plainTextToken;
    }

    public function createForId(string $userId): string
    {
        /** @var EloquentUser $model */
        $model = EloquentUser::findOrFail($userId);

        return $model->createToken('auth_token')->plainTextToken;
    }
}

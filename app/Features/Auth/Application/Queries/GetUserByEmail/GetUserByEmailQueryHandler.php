<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Queries\GetUserByEmail;

use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\Entities\User;

final readonly class GetUserByEmailQueryHandler {
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(GetUserByEmailQuery $query): ?User
    {
        return $this->userRepository->findByEmail(Email::fromString($query->email)) ?? null;
    }
}
<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommand;
use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommandHandler;
use App\Features\Auth\Infrastructure\Mappers\UserMapper;

final readonly class FortifyRegisterUserCreator implements CreatesNewUsers {
    public function __construct(
        private RegisterUserCommandHandler $handler,
        private UserMapper $userMapper
    ) {}

    public function create(array $input): Authenticatable
    {
        $command = new RegisterUserCommand(
            name: $input['name'],
            email: $input['email'],
            password: $input['password'],
        );

        $user = ($this->handler)($command);
        
        return $this->userMapper->toModel($user);
    }
}
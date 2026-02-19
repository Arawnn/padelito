<?php

declare(strict_types=1);

namespace App\Features\Auth\Infrastructure\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Shared\Application\Bus\QueryBusInterface;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Features\Auth\Infrastructure\Mappers\UserMapper;
use App\Features\Auth\Domain\Exceptions\UserNotFoundException;
use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommand;
use App\Features\Auth\Application\Queries\GetUserByEmail\GetUserByEmailQuery;

final readonly class FortifyRegisterUserCreator implements CreatesNewUsers {
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
        private UserMapper $userMapper
    ) {}

    public function create(array $input): Authenticatable {
        $command = new RegisterUserCommand(
            name: $input['name'],
            email: $input['email'],
            password: $input['password'],
        );

        $this->commandBus->dispatch($command);
        $user = $this->queryBus->ask(new GetUserByEmailQuery($command->email));
        
        if (!$user) {
            throw UserNotFoundException::fromEmail(Email::fromString($command->email));
        }
        return $this->userMapper->toModel($user);
    }
}
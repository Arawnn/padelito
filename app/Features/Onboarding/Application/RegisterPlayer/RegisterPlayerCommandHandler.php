<?php

declare(strict_types=1);

namespace App\Features\Onboarding\Application\RegisterPlayer;

use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommand;
use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommandHandler;
use App\Features\Player\Application\Commands\InitializePlayerProfile\InitializePlayerProfileCommand;
use App\Features\Player\Application\Commands\InitializePlayerProfile\InitializePlayerProfileCommandHandler;

/**
 * Orchestration du flow d'inscription.
 * Garantit atomiquement la création du compte Auth et du profil Player.
 * La transaction est ouverte par le command bus appelant.
 */
final readonly class RegisterPlayerCommandHandler
{
    public function __construct(
        private RegisterUserCommandHandler $registerUserHandler,
        private InitializePlayerProfileCommandHandler $initializePlayerProfileHandler,
    ) {}

    public function __invoke(RegisterPlayerCommand $command): RegisterPlayerResult
    {
        $user = ($this->registerUserHandler)(new RegisterUserCommand(
            name: $command->name,
            email: $command->email,
            password: $command->password,
        ));

        ($this->initializePlayerProfileHandler)(new InitializePlayerProfileCommand(
            userId: $user->id()->value(),
            displayName: $user->name()->value(),
        ));

        return new RegisterPlayerResult(
            userId: $user->id()->value(),
            name: $user->name()->value(),
            email: $user->email()->value(),
        );
    }
}

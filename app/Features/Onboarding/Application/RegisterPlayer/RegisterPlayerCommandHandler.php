<?php

declare(strict_types=1);

namespace App\Features\Onboarding\Application\RegisterPlayer;

use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommand;
use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommandHandler;
use App\Features\Player\Application\Commands\InitializePlayerProfile\InitializePlayerProfileCommand;
use App\Features\Player\Application\Commands\InitializePlayerProfile\InitializePlayerProfileCommandHandler;
use App\Shared\Application\Transaction\TransactionManagerInterface;

/**
 * Transaction root du flow d'inscription.
 * Garantit atomiquement la création du compte Auth et du profil Player.
 * afterCommit() des sous-handlers se déclenchent au commit de cette transaction.
 */
final readonly class RegisterPlayerCommandHandler
{
    public function __construct(
        private RegisterUserCommandHandler $registerUserHandler,
        private InitializePlayerProfileCommandHandler $initializePlayerProfileHandler,
        private TransactionManagerInterface $transactionManager,
    ) {}

    public function __invoke(RegisterPlayerCommand $command): RegisterPlayerResult
    {
        return $this->transactionManager->run(function () use ($command): RegisterPlayerResult {
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
        });
    }
}

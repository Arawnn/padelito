<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\RegisterUser;

use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Exceptions\UserAlreadyExistsException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Name;
use App\Features\Auth\Domain\ValueObjects\Password;
use App\Shared\Application\Transaction\TransactionManagerInterface;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Contracts\UuidGeneratorInterface;

final readonly class RegisterUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TransactionManagerInterface $transactionManager,
        private PasswordHasherInterface $passwordHasher,
        private UuidGeneratorInterface $uuidGenerator,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    /**
     * TODO: return a DTO instead of exposing the aggregate root
     */
    public function __invoke(RegisterUserCommand $command): User
    {
        $email = Email::fromString($command->email);

        if ($this->userRepository->findByEmail($email) !== null) {
            throw UserAlreadyExistsException::fromEmail($email);
        }

        $id = Id::fromString($this->uuidGenerator->generate());

        return $this->transactionManager->run(
            fn () => $this->createUser($command, $id, $email)
        );
    }

    private function createUser(RegisterUserCommand $command, Id $id, Email $email): User
    {
        $user = User::register(
            id: $id,
            name: Name::fromString($command->name),
            email: $email,
            password: $this->passwordHasher->hash(
                Password::fromPlainText($command->password)
            ),
        );

        $this->userRepository->save($user);

        $domainEvents = $user->pullDomainEvents();

        $this->transactionManager->afterCommit(
            fn () => $this->eventDispatcher->dispatchEvents($domainEvents)
        );

        return $user;
    }
}

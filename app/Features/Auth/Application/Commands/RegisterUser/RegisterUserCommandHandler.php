<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\RegisterUser;

use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\Exceptions\UserAlreadyExistException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Name;
use App\Features\Auth\Domain\ValueObjects\Password;
use App\Shared\Application\Result;
use App\Shared\Application\Transaction\TransactionManagerInterface;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Contracts\UuidGeneratorInterface;

final readonly class RegisterUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TransactionManagerInterface $tx,
        private PasswordHasherInterface $passwordHasher,
        private UuidGeneratorInterface $uuidGenerator,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    /**
     * @return Result<User>
     */
    public function __invoke(RegisterUserCommand $command): Result
    {
        return Result::try(fn () => Email::fromString($command->email))
            ->flatMap(fn (Email $email) => $this->register($command, $email));
    }

    /**
     * @return Result<User>
     */
    private function register(RegisterUserCommand $command, Email $email): Result
    {
        if ($this->userRepository->findByEmail($email)) {
            return Result::fail(UserAlreadyExistException::fromEmail($email));
        }

        return Result::try(function () use ($command, $email) {
            $id = Id::fromString($this->uuidGenerator->generate());

            return $this->tx->run(function () use ($command, $id, $email) {
                $hashedPassword = $this->passwordHasher->hash(
                    Password::fromPlainText($command->password)
                );

                $user = User::register(
                    id: $id,
                    name: Name::fromString($command->name),
                    email: $email,
                    password: $hashedPassword,
                );

                $this->userRepository->create($user);
                $this->tx->afterCommit(function () use ($user) {
                    $this->eventDispatcher->dispatchEvents($user->pullDomainEvents());
                });

                return $user;
            });
        });
    }
}

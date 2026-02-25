<?php

declare(strict_types=1);

namespace App\Features\Auth\Application\Commands\RegisterUser;

use App\Features\Auth\Domain\Entities\User;
use App\Features\Auth\Domain\ValueObjects\Id;
use App\Features\Auth\Domain\ValueObjects\Name;
use App\Features\Auth\Domain\ValueObjects\Email;
use App\Features\Auth\Domain\ValueObjects\Password;
use App\Shared\Domain\Contracts\UuidGeneratorInterface;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Features\Auth\Domain\Exceptions\UserAlreadyExistException;
use App\Features\Auth\Domain\Repositories\UserRepositoryInterface;
use App\Features\Auth\Application\Commands\RegisterUser\RegisterUserCommand;
use App\Features\Auth\Domain\Contracts\PasswordHasherInterface;
use App\Features\Auth\Domain\Exceptions\InvalidPasswordException;
use App\Shared\Application\Transaction\TransactionManagerInterface;
use App\Shared\Domain\ValueObjects\Result;

final readonly class RegisterUserCommandHandler {
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
        $id = Id::fromString($this->uuidGenerator->generate());

        if ($this->userRepository->findById($id)) {
            return Result::fail(
                UserAlreadyExistException::fromId($id)
            );
        }

        if ($this->userRepository->findByEmail(Email::fromString($command->email))) {
            return Result::fail(
                UserAlreadyExistException::fromEmail(Email::fromString($command->email))
            );
        }

        $user = $this->tx->run(function() use ($command, $id) {
            try {
                $hashedPassword = $this->passwordHasher->hash(
                    Password::fromPlainText($command->password)
                );
            } catch (InvalidPasswordException $e) {
                return Result::fail(InvalidPasswordException::fromViolations($e->violations()));
            }
           
            $user = User::register(
                id: $id,
                name: Name::fromString($command->name),
                email: Email::fromString($command->email),
                password: $hashedPassword,
            );
    
            $this->userRepository->create($user);
            return $user;
        });

        $this->tx->afterCommit(function() use ($user) {
            $this->eventDispatcher->dispatchEvents($user->pullDomainEvents());
        });

        return Result::ok($user);
    }
}
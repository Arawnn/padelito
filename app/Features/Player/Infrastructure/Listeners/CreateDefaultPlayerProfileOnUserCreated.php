<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Listeners;

use App\Features\Auth\Domain\Events\UserCreated;
use App\Features\Player\Application\Commands\CreatePlayerProfile\CreatePlayerProfileCommand;
use App\Features\Player\Domain\Enums\PlayerLevelEnum;
use App\Features\Player\Domain\Services\UsernameGeneratorService;
use App\Shared\Application\Bus\CommandBusInterface;

final readonly class CreateDefaultPlayerProfileOnUserCreated
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private UsernameGeneratorService $usernameGenerator,
    ) {}

    public function __invoke(UserCreated $event): void
    {
        $username = $this->usernameGenerator->generateFrom($event->name);

        $this->commandBus->dispatch(new CreatePlayerProfileCommand(
            userId: $event->userId,
            username: $username->value(),
            level: PlayerLevelEnum::BEGINNER->value,
            displayName: $event->name,
            bio: null,
            location: null,
            dominantHand: null,
            preferredPosition: null,
        ));
    }
}

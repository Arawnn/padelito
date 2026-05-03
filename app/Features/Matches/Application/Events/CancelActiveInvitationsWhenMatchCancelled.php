<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Events;

use App\Features\Matches\Domain\Events\MatchCancelled;
use App\Features\Matches\Domain\Repositories\MatchInvitationRepositoryInterface;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Shared\Domain\Contracts\EventDispatcherInterface;
use App\Shared\Domain\Events\DomainEventCollection;
use App\Shared\Domain\Events\DomainEventSubscriberInterface;

final readonly class CancelActiveInvitationsWhenMatchCancelled implements DomainEventSubscriberInterface
{
    public function __construct(
        private MatchInvitationRepositoryInterface $invitationRepository,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public static function subscribedTo(): array
    {
        return [MatchCancelled::class];
    }

    public function __invoke(MatchCancelled $event): void
    {
        $events = new DomainEventCollection;

        foreach ($this->invitationRepository->findCancellableByMatchId(MatchId::fromString($event->matchId)) as $invitation) {
            $invitation->cancel();
            $this->invitationRepository->save($invitation);

            foreach ($invitation->pullDomainEvents() as $invitationEvent) {
                $events->add($invitationEvent);
            }
        }

        $this->eventDispatcher->dispatchEvents($events);
    }
}

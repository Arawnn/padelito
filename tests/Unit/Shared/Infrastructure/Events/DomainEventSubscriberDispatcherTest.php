<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Infrastructure\Events;

use App\Shared\Domain\Events\DomainEvent;
use App\Shared\Domain\Events\DomainEventCollection;
use App\Shared\Domain\Events\DomainEventSubscriberInterface;
use App\Shared\Infrastructure\Events\DomainEventSubscriberDispatcher;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class DomainEventSubscriberDispatcherTest extends TestCase
{
    public function test_it_dispatches_events_to_matching_subscribers_only(): void
    {
        $probe = new \stdClass;
        $subscriber = new MatchingTestSubscriber($probe);
        $ignoredSubscriber = new IgnoredTestSubscriber($probe);

        $dispatcher = new DomainEventSubscriberDispatcher([$subscriber, $ignoredSubscriber]);

        $dispatcher->dispatch(new TestDomainEvent('aggregate-id'));

        $this->assertSame(1, $probe->handledCount);
        $this->assertFalse($probe->ignoredWasCalled ?? false);
    }

    public function test_it_dispatches_event_collections(): void
    {
        $probe = new \stdClass;
        $subscriber = new MatchingTestSubscriber($probe);
        $events = new DomainEventCollection;
        $events->add(new TestDomainEvent('aggregate-id-1'));
        $events->add(new TestDomainEvent('aggregate-id-2'));

        $dispatcher = new DomainEventSubscriberDispatcher([$subscriber]);

        $dispatcher->dispatchEvents($events);

        $this->assertSame(2, $probe->handledCount);
    }
}

final class TestDomainEvent extends DomainEvent
{
    public static function eventName(): string
    {
        return 'test.domain_event';
    }

    public function toPrimitives(): array
    {
        return [];
    }
}

final class OtherTestDomainEvent extends DomainEvent
{
    public static function eventName(): string
    {
        return 'test.other_domain_event';
    }

    public function toPrimitives(): array
    {
        return [];
    }
}

final readonly class MatchingTestSubscriber implements DomainEventSubscriberInterface
{
    public function __construct(private \stdClass $probe) {}

    public static function subscribedTo(): array
    {
        return [TestDomainEvent::class];
    }

    public function __invoke(TestDomainEvent $event): void
    {
        $this->probe->handledCount = ($this->probe->handledCount ?? 0) + 1;
    }
}

final readonly class IgnoredTestSubscriber implements DomainEventSubscriberInterface
{
    public function __construct(private \stdClass $probe) {}

    public static function subscribedTo(): array
    {
        return [OtherTestDomainEvent::class];
    }

    public function __invoke(OtherTestDomainEvent $event): void
    {
        $this->probe->ignoredWasCalled = true;
    }
}

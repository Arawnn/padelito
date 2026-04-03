<?php

declare(strict_types=1);

namespace App\Shared\Domain\Events;

/**
 * @implements \IteratorAggregate<int, DomainEvent>
 */
final class DomainEventCollection implements \Countable, \IteratorAggregate
{
    /** @var list<DomainEvent> */
    private array $events = [];

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->events);
    }

    public function count(): int
    {
        return count($this->events);
    }

    public function isEmpty(): bool
    {
        return $this->events === [];
    }

    public function add(DomainEvent $event): void
    {
        $this->events[] = $event;
    }

    public function first(): DomainEvent
    {
        if ($this->isEmpty()) {
            throw new \LogicException('Collection is empty');
        }

        return $this->events[0];
    }

    /**
     * @return list<DomainEvent>
     */
    public function toArray(): array
    {
        return $this->events;
    }
}

<?php
declare(strict_types=1);

namespace App\Shared\Domain\Events;

final class DomainEventCollection implements \IteratorAggregate, \Countable {
    private array $events = [];

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->events);
    }

    public function count(): int
    {
        return count($this->events);
    }

    public function add(DomainEvent $event): void
    {
        $this->events[] = $event;
    }
}
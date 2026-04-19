<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\ValueObjects;

use App\Features\Matches\Domain\Exceptions\InvalidSetsDetailException;

final readonly class SetsDetail
{
    /** @param list<array{a: int, b: int}> $sets */
    private function __construct(private array $sets) {}

    /**
     * @param  list<array{a: int, b: int}>  $sets
     */
    public static function fromArray(array $sets): self
    {
        self::validate($sets);

        return new self($sets);
    }

    /** @return list<array{a: int, b: int}> */
    public function sets(): array
    {
        return $this->sets;
    }

    public function teamASetsWon(): int
    {
        return count(array_filter($this->sets, fn (array $set) => $set['a'] > $set['b']));
    }

    public function teamBSetsWon(): int
    {
        return count(array_filter($this->sets, fn (array $set) => $set['b'] > $set['a']));
    }

    public function setCount(): int
    {
        return count($this->sets);
    }

    public function hasWinner(int $setsToWin): bool
    {
        return $this->teamASetsWon() >= $setsToWin || $this->teamBSetsWon() >= $setsToWin;
    }

    /** @param list<array{a: int, b: int}> $sets */
    private static function validate(array $sets): void
    {
        $violations = [];

        if (empty($sets)) {
            $violations[] = 'Sets detail cannot be empty';
        }

        if (count($sets) > 5) {
            $violations[] = 'A match cannot have more than 5 sets';
        }

        foreach ($sets as $i => $set) {
            if (! isset($set['a'], $set['b'])) {
                $violations[] = "Set {$i} must have keys 'a' and 'b'";

                continue;
            }

            if (! is_int($set['a']) || ! is_int($set['b'])) {
                $violations[] = "Set {$i} scores must be integers";

                continue;
            }

            if ($set['a'] < 0 || $set['b'] < 0) {
                $violations[] = "Set {$i} scores cannot be negative";
            }
        }

        if (! empty($violations)) {
            throw InvalidSetsDetailException::fromViolations($violations);
        }
    }
}

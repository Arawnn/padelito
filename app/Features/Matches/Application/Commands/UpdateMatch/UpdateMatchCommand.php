<?php

declare(strict_types=1);

namespace App\Features\Matches\Application\Commands\UpdateMatch;

final readonly class UpdateMatchCommand
{
    /**
     * @param  list<array{a: int, b: int}>|null  $setsDetail
     * @param  list<string>|null  $fields
     */
    public function __construct(
        public string $matchId,
        public string $requesterId,
        public ?string $courtName = null,
        public ?string $matchDate = null,
        public ?string $notes = null,
        public ?string $matchFormat = null,
        public ?string $matchType = null,
        public ?array $setsDetail = null,
        public ?int $setsToWin = null,
        private ?array $fields = null,
    ) {}

    public function has(string $field): bool
    {
        if ($this->fields !== null) {
            return in_array($field, $this->fields, true);
        }

        return match ($field) {
            'courtName' => $this->courtName !== null,
            'matchDate' => $this->matchDate !== null,
            'notes' => $this->notes !== null,
            'matchFormat' => $this->matchFormat !== null,
            'matchType' => $this->matchType !== null,
            'setsDetail' => $this->setsDetail !== null,
            'setsToWin' => $this->setsToWin !== null,
            default => false,
        };
    }
}

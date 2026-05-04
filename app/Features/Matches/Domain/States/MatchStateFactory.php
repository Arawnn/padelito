<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\States;

use App\Features\Matches\Domain\Enums\MatchStatusEnum;
use App\Features\Matches\Domain\ValueObjects\MatchStatus;

final readonly class MatchStateFactory
{
    public static function fromStatus(MatchStatus $status): MatchStateInterface
    {
        return match ($status->value()) {
            MatchStatusEnum::PENDING => new MatchStatusPendingState,
            MatchStatusEnum::VALIDATED => new MatchStatusValidatedState,
            MatchStatusEnum::CANCELLED => new MatchStatusCancelledState,
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Enums;

enum MatchStatusEnum: string
{
    case PENDING = 'pending';
    case VALIDATED = 'validated';
    case CANCELLED = 'cancelled';
}

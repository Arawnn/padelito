<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Enums;

enum MatchFormatEnum: string
{
    case SINGLES = 'singles';
    case DOUBLES = 'doubles';
}

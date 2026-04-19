<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Enums;

enum MatchTypeEnum: string
{
    case FRIENDLY = 'friendly';
    case RANKED = 'ranked';
}

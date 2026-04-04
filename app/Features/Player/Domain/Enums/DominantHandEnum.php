<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Enums;

enum DominantHandEnum: string
{
    case LEFT = 'left';
    case RIGHT = 'right';
    case AMBIDEXTROUS = 'ambidextrous';
}

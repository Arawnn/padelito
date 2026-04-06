<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Enums;

enum PlayerLevelEnum: string
{
    case BEGINNER = 'beginner';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';
    case CONFIRMED = 'confirmed';
    case EXPERT = 'expert';
}

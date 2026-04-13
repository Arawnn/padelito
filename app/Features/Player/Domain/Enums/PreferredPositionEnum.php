<?php

declare(strict_types=1);

namespace App\Features\Player\Domain\Enums;

enum PreferredPositionEnum: string
{
    case BACK = 'back';
    case NET = 'net';
    case ANY = 'any';
}

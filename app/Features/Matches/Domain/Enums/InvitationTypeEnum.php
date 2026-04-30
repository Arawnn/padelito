<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Enums;

enum InvitationTypeEnum: string
{
    case PARTNER = 'partner';
    case OPPONENT = 'opponent';
}

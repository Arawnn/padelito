<?php

declare(strict_types=1);

namespace App\Features\Matches\Domain\Enums;

enum InvitationStatusEnum: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case CANCELLED = 'cancelled';
}

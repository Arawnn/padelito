<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Http\v1\Resources;

use App\Features\Matches\Domain\Entities\MatchInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MatchInvitation */
final class MatchInvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id()->value(),
            'match_id' => $this->matchId()->value(),
            'invitee_id' => $this->inviteeId()->value(),
            'team' => $this->team()->value()->value,
            'position' => $this->position(),
            'status' => $this->status()->value()->value,
            'invited_at' => $this->invitedAt()->format('Y-m-d H:i:s'),
            'responded_at' => $this->respondedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}

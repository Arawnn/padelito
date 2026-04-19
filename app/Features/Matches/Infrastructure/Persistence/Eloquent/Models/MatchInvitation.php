<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $match_id
 * @property string $invitee_id
 * @property string $team
 * @property int $position
 * @property string $status
 * @property string $invited_at
 * @property string|null $responded_at
 */
class MatchInvitation extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'string';

    protected $table = 'match_invitations';

    protected $fillable = [
        'id',
        'match_id',
        'invitee_id',
        'team',
        'position',
        'status',
        'invited_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }
}

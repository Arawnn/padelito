<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $player_id
 * @property string $match_id
 * @property int $elo_before
 * @property int $elo_after
 * @property int $elo_change
 * @property string $recorded_at
 */
class EloHistory extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'string';

    protected $table = 'elo_history';

    protected $fillable = [
        'id',
        'player_id',
        'match_id',
        'elo_before',
        'elo_after',
        'elo_change',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'elo_before' => 'integer',
            'elo_after' => 'integer',
            'elo_change' => 'integer',
        ];
    }
}

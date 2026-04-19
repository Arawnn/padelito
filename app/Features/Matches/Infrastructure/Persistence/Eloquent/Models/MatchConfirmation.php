<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $match_id
 * @property string $player_id
 * @property string $confirmed_at
 */
class MatchConfirmation extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'string';

    protected $table = 'match_confirmations';

    protected $fillable = ['id', 'match_id', 'player_id', 'confirmed_at'];
}

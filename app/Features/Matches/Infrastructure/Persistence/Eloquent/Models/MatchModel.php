<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string|null $match_date
 * @property string|null $court_name
 * @property string $match_type
 * @property string $match_format
 * @property string $status
 * @property string $team_a_player1_id
 * @property string|null $team_a_player2_id
 * @property string|null $team_b_player1_id
 * @property string|null $team_b_player2_id
 * @property array|null $sets_detail
 * @property int|null $team_a_score
 * @property int|null $team_b_score
 * @property string|null $notes
 * @property int|null $team_a_elo_before
 * @property int|null $team_b_elo_before
 * @property int|null $elo_change
 * @property string $created_by
 */
class MatchModel extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'matches';

    protected $fillable = [
        'id',
        'match_date',
        'court_name',
        'match_type',
        'match_format',
        'status',
        'team_a_player1_id',
        'team_a_player2_id',
        'team_b_player1_id',
        'team_b_player2_id',
        'sets_detail',
        'team_a_score',
        'team_b_score',
        'notes',
        'team_a_elo_before',
        'team_b_elo_before',
        'elo_change',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sets_detail' => 'array',
            'team_a_score' => 'integer',
            'team_b_score' => 'integer',
            'team_a_elo_before' => 'integer',
            'team_b_elo_before' => 'integer',
            'elo_change' => 'integer',
        ];
    }

    public function confirmations(): HasMany
    {
        return $this->hasMany(MatchConfirmation::class, 'match_id');
    }
}

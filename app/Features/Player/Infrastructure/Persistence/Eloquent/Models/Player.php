<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Persistence\Eloquent\Models;

use App\Features\Auth\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Player extends Model
{
    /**
     * Player profile id matches {@see User} id (1:1).
     */
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'players';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'username',
        'dominant_hand',
        'preferred_position',
        'location',
        'display_name',
        'bio',
        'avatar_url',
        'total_wins',
        'total_losses',
        'elo_rating',
        'current_streak',
        'best_streak',
        'padel_coins',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_wins' => 'integer',
            'total_losses' => 'integer',
            'elo_rating' => 'integer',
            'current_streak' => 'integer',
            'best_streak' => 'integer',
            'padel_coins' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }
}

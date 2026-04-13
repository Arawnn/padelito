<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Persistence\Eloquent\Models;

use App\Features\Auth\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $username
 * @property string $level
 * @property string|null $display_name
 * @property string|null $bio
 * @property string|null $avatar_url
 * @property string|null $dominant_hand
 * @property string|null $preferred_position
 * @property string|null $location
 * @property int $elo_rating
 * @property int $total_wins
 * @property int $total_losses
 * @property int $current_streak
 * @property int $best_streak
 * @property int $padel_coins
 * @property bool $is_public
 */
class Player extends Model
{
    /**
     * Player profile id matches {@see User} id (1:1).
     */
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'profiles';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'username',
        'level',
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
        'is_public',
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
            'is_public' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }
}

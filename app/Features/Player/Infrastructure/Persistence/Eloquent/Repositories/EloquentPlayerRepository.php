<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Persistence\Eloquent\Repositories;

use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\Username;
use App\Features\Player\Infrastructure\Persistence\Eloquent\Mappers\PlayerMapper;
use App\Features\Player\Infrastructure\Persistence\Eloquent\Models\Player as EloquentPlayer;

class EloquentPlayerRepository implements PlayerRepositoryInterface
{
    public function __construct(
        private readonly PlayerMapper $mapper,
    ) {}

    public function findById(Id $id): ?Player
    {
        $model = EloquentPlayer::find($id->value());

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByUsername(Username $username): ?Player
    {
        $model = EloquentPlayer::where('username', $username->value())->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(Player $player): void
    {
        $data = $this->mapper->toPersistence($player);

        EloquentPlayer::updateOrCreate(
            ['id' => $data['id']],
            $data,
        );
    }
}

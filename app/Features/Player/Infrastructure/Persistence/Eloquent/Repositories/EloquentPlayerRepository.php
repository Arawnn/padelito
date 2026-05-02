<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Persistence\Eloquent\Repositories;

use App\Features\Player\Domain\Entities\Player;
use App\Features\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Features\Player\Domain\ValueObjects\Id;
use App\Features\Player\Domain\ValueObjects\Username;
use App\Features\Player\Infrastructure\Persistence\Eloquent\Mappers\PlayerMapper;
use App\Features\Player\Infrastructure\Persistence\Eloquent\Models\Player as EloquentPlayer;

final class EloquentPlayerRepository implements PlayerRepositoryInterface
{
    public function __construct(
        private readonly PlayerMapper $mapper,
    ) {}

    public function findById(Id $id): ?Player
    {
        $model = EloquentPlayer::find($id->value());

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return EloquentPlayer::query()
            ->whereIn('id', array_values(array_unique(array_map(fn (Id $id): string => $id->value(), $ids))))
            ->get()
            ->mapWithKeys(fn (EloquentPlayer $model): array => [$model->id => $this->mapper->toDomain($model)])
            ->all();
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

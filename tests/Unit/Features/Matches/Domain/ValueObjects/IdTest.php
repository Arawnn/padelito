<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Matches\Domain\ValueObjects;

use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchInvitationId;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Shared\Domain\Exceptions\InvalidUuidException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class IdTest extends TestCase
{
    /**
     * @param  class-string  $idClass
     */
    #[DataProvider('idClasses')]
    public function test_it_accepts_valid_uuid_strings(string $idClass): void
    {
        $id = $idClass::fromString('00000000-0000-0000-0000-000000000001');

        $this->assertSame('00000000-0000-0000-0000-000000000001', $id->value());
    }

    /**
     * @param  class-string  $idClass
     */
    #[DataProvider('idClasses')]
    public function test_it_rejects_invalid_uuid_strings(string $idClass): void
    {
        $this->expectException(InvalidUuidException::class);

        $idClass::fromString('not-a-uuid');
    }

    /**
     * @return array<string, array{class-string}>
     */
    public static function idClasses(): array
    {
        return [
            'match id' => [MatchId::class],
            'match invitation id' => [MatchInvitationId::class],
            'player id' => [PlayerId::class],
        ];
    }
}

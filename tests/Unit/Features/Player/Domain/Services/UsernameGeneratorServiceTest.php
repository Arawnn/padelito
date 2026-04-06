<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Domain\Services;

use App\Features\Player\Domain\Services\UsernameGeneratorService;
use Tests\Shared\Mother\Fake\InMemoryPlayerRepository;
use Tests\Shared\Mother\PlayerMother;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class UsernameGeneratorServiceTest extends TestCase
{
    private InMemoryPlayerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryPlayerRepository;
    }

    public function test_it_generates_username_from_full_name(): void
    {
        $service = $this->makeService();

        $username = $service->generateFrom('Jean Dupont');

        $this->assertEquals('jean_dupont', $username->value());
    }

    public function test_it_transliterates_accents(): void
    {
        $username = $this->makeService()->generateFrom('Élodie Müller');

        $this->assertMatchesRegularExpression('/^[a-z0-9_]+$/', $username->value());
        $this->assertStringNotContainsString('é', $username->value());
    }

    public function test_it_appends_suffix_when_username_is_taken(): void
    {
        $existing = PlayerMother::create()->withUsername('jean_dupont')->build();
        $this->repository->save($existing);

        $username = $this->makeService()->generateFrom('Jean Dupont');

        $this->assertEquals('jean_dupont_1', $username->value());
    }

    public function test_it_increments_suffix_until_unique(): void
    {
        foreach (['jean_dupont', 'jean_dupont_1', 'jean_dupont_2'] as $i => $taken) {
            $this->repository->save(
                PlayerMother::create()
                    ->withId('00000000-0000-0000-0000-00000000000'.(string) ($i + 1))
                    ->withUsername($taken)
                    ->build()
            );
        }

        $username = $this->makeService()->generateFrom('Jean Dupont');

        $this->assertEquals('jean_dupont_3', $username->value());
    }

    public function test_it_handles_empty_name_with_player_fallback(): void
    {
        $username = $this->makeService()->generateFrom('');

        $this->assertEquals('player', $username->value());
    }

    public function test_it_truncates_long_names(): void
    {
        $longName = str_repeat('A', 40);

        $username = $this->makeService()->generateFrom($longName);

        $this->assertLessThanOrEqual(30, strlen($username->value()));
    }

    public function test_generated_username_matches_format_rules(): void
    {
        $names = ['Jean-Pierre Léroy', 'María García', 'O\'Brien', '  Spaces  '];

        foreach ($names as $name) {
            $username = $this->makeService()->generateFrom($name);
            $this->assertMatchesRegularExpression('/^[a-z0-9_]{3,30}$/', $username->value(), "Failed for: $name");
        }
    }

    private function makeService(): UsernameGeneratorService
    {
        return new UsernameGeneratorService($this->repository);
    }
}

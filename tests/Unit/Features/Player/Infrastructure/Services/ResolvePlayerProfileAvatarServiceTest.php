<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Infrastructure\Services;

use App\Features\Player\Infrastructure\Services\DefaultAvatarProvisioner;
use App\Shared\Domain\Contracts\FileStorageInterface;
use App\Shared\Domain\Contracts\ImageFetchInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ResolvePlayerProfileAvatarServiceTest extends TestCase
{
    private static function minimalPng(): string
    {
        $b = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDQAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);

        return is_string($b) ? $b : '';
    }

    public function test_it_fetches_ui_avatars_when_no_avatar_provided(): void
    {
        $capturedUrl = null;
        $fetch = $this->createStub(ImageFetchInterface::class);
        $fetch->method('fetch')->willReturnCallback(function (string $url) use (&$capturedUrl): string {
            $capturedUrl = $url;

            return self::minimalPng();
        });

        $storage = $this->createStub(FileStorageInterface::class);
        $storage->method('upload')->willReturn('https://cdn.example/avatars/u1/x.png');

        $result = (new DefaultAvatarProvisioner($storage, $fetch))
            ->provision(userId: 'u1', displayName: 'Jean Dupont', avatar: null);

        self::assertTrue($result->isOk());
        self::assertSame('https://cdn.example/avatars/u1/x.png', $result->value());
        self::assertNotNull($capturedUrl);
        assert(is_string($capturedUrl));
        self::assertStringStartsWith('https://ui-avatars.com/api/', $capturedUrl);
        self::assertStringContainsString('name=', $capturedUrl);
        self::assertStringContainsString('Jean', $capturedUrl);
    }

    public function test_it_uses_question_mark_in_ui_avatars_url_when_display_name_is_blank(): void
    {
        $capturedUrl = null;
        $fetch = $this->createStub(ImageFetchInterface::class);
        $fetch->method('fetch')->willReturnCallback(function (string $url) use (&$capturedUrl): string {
            $capturedUrl = $url;

            return self::minimalPng();
        });

        $storage = $this->createStub(FileStorageInterface::class);
        $storage->method('upload')->willReturn('https://cdn.example/y.png');

        (new DefaultAvatarProvisioner($storage, $fetch))
            ->provision(userId: 'u1', displayName: '   ', avatar: null);

        self::assertNotNull($capturedUrl);
        assert(is_string($capturedUrl));
        self::assertStringContainsString('name=%3F', $capturedUrl);
    }
}

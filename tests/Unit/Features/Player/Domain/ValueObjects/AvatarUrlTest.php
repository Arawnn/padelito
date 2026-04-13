<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Player\Domain\ValueObjects;

use App\Features\Player\Domain\Exceptions\InvalidAvatarUrlException;
use App\Features\Player\Domain\ValueObjects\AvatarUrl;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class AvatarUrlTest extends TestCase
{
    public function test_it_accepts_a_valid_https_url(): void
    {
        $url = AvatarUrl::fromString('https://example.com/avatar.jpg');

        $this->assertEquals('https://example.com/avatar.jpg', $url->value());
    }

    public function test_it_accepts_a_valid_http_url(): void
    {
        $url = AvatarUrl::fromString('http://localhost/storage/avatars/test.jpg');

        $this->assertEquals('http://localhost/storage/avatars/test.jpg', $url->value());
    }

    public function test_it_accepts_a_url_with_path_and_query(): void
    {
        $url = AvatarUrl::fromString('https://cdn.example.com/avatars/user-123.png?v=2');

        $this->assertEquals('https://cdn.example.com/avatars/user-123.png?v=2', $url->value());
    }

    public function test_it_rejects_an_empty_string(): void
    {
        $this->expectException(InvalidAvatarUrlException::class);
        AvatarUrl::fromString('');
    }

    public function test_it_rejects_a_plain_string(): void
    {
        $this->expectException(InvalidAvatarUrlException::class);
        AvatarUrl::fromString('not-a-url');
    }

    public function test_it_rejects_a_url_without_scheme(): void
    {
        $this->expectException(InvalidAvatarUrlException::class);
        AvatarUrl::fromString('example.com/avatar.jpg');
    }

    public function test_exception_message_describes_the_violation(): void
    {
        $this->expectException(InvalidAvatarUrlException::class);
        $this->expectExceptionMessage('Avatar URL is not a valid URL');
        AvatarUrl::fromString('bad-value');
    }
}

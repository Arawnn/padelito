<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Infrastructure\Http;

use App\Shared\Domain\Exceptions\ImageFetchFailedException;
use App\Shared\Infrastructure\Http\SafeHttpImageFetcher;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SafeHttpImageFetcherTest extends TestCase
{
    private SafeHttpImageFetcher $fetcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fetcher = new SafeHttpImageFetcher;
    }

    public function test_it_rejects_non_https_urls(): void
    {
        $this->expectException(ImageFetchFailedException::class);
        $this->fetcher->fetch('http://example.com/a.png');
    }

    public function test_it_rejects_localhost_host(): void
    {
        $this->expectException(ImageFetchFailedException::class);
        $this->fetcher->fetch('https://localhost/p.png');
    }

    public function test_it_rejects_loopback_ip_literal(): void
    {
        $this->expectException(ImageFetchFailedException::class);
        $this->fetcher->fetch('https://127.0.0.1/p.png');
    }

    public function test_it_rejects_non_image_content_type(): void
    {
        Http::fake([
            'https://example.com/*' => Http::response('hello', 200, ['Content-Type' => 'text/plain']),
        ]);

        $this->expectException(ImageFetchFailedException::class);
        $this->fetcher->fetch('https://example.com/file.png');
    }
}

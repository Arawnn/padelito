<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use App\Shared\Domain\Contracts\ImageFetchInterface;
use App\Shared\Domain\Exceptions\ImageFetchFailedException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class SafeHttpImageFetcher implements ImageFetchInterface
{
    private const MAX_BYTES = 2097152;

    private const MAX_REDIRECTS = 4;

    public function fetch(string $url): string
    {
        $currentUrl = trim($url);
        if ($currentUrl === '') {
            throw ImageFetchFailedException::because('Avatar URL is empty.');
        }

        if (! Str::startsWith($currentUrl, 'https://')) {
            throw ImageFetchFailedException::because('Avatar URL must use HTTPS.');
        }

        for ($redirect = 0; $redirect < self::MAX_REDIRECTS; $redirect++) {
            $this->assertUrlSafe($currentUrl);

            $response = Http::timeout(15)
                ->connectTimeout(5)
                ->withoutRedirecting()
                ->get($currentUrl);

            if ($response->redirect()) {
                $location = $response->header('Location');
                if ($location === null || $location === '') {
                    throw ImageFetchFailedException::because('Redirect response missing Location header.');
                }
                $currentUrl = $this->resolveRedirectUrl($currentUrl, $location);

                continue;
            }

            if (! $response->successful()) {
                throw ImageFetchFailedException::because('Could not download image from URL.');
            }

            $body = $response->body();
            if (strlen($body) > self::MAX_BYTES) {
                throw ImageFetchFailedException::because('Image exceeds maximum allowed size.');
            }

            $contentType = (string) $response->header('Content-Type');
            $this->assertAllowedImageContentType($contentType);
            $this->assertImageMagicBytes($body);

            return $body;
        }

        throw ImageFetchFailedException::because('Too many redirects while fetching image.');
    }

    private function assertUrlSafe(string $url): void
    {
        $parts = parse_url($url);
        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            throw ImageFetchFailedException::because('Invalid avatar URL.');
        }

        if (($parts['scheme'] ?? '') !== 'https') {
            throw ImageFetchFailedException::because('Avatar URL must use HTTPS.');
        }

        $host = strtolower($parts['host']);
        if ($host === 'localhost' || Str::endsWith($host, '.localhost') || Str::endsWith($host, '.local')) {
            throw ImageFetchFailedException::because('Avatar URL host is not allowed.');
        }

        if (filter_var($parts['host'], FILTER_VALIDATE_IP)) {
            if (! $this->isPublicIp($parts['host'])) {
                throw ImageFetchFailedException::because('Avatar URL must resolve to a public network address.');
            }

            return;
        }

        $records = @dns_get_record($parts['host'], DNS_A | DNS_AAAA);
        if ($records === false || $records === []) {
            throw ImageFetchFailedException::because('Could not resolve avatar URL host.');
        }

        foreach ($records as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;
            if ($ip !== null && ! $this->isPublicIp($ip)) {
                throw ImageFetchFailedException::because('Avatar URL must resolve to a public network address.');
            }
        }
    }

    private function isPublicIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    private function resolveRedirectUrl(string $currentUrl, string $location): string
    {
        if (Str::startsWith($location, 'https://') || Str::startsWith($location, 'http://')) {
            if (! Str::startsWith($location, 'https://')) {
                throw ImageFetchFailedException::because('Redirect must target HTTPS.');
            }

            return $location;
        }

        $base = parse_url($currentUrl);
        if ($base === false || ! isset($base['scheme'], $base['host'])) {
            throw ImageFetchFailedException::because('Invalid redirect base URL.');
        }

        $path = $location;
        if (str_starts_with($path, '//')) {
            return 'https:'.$path;
        }

        if (! str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        $port = isset($base['port']) ? ':'.$base['port'] : '';

        return $base['scheme'].'://'.$base['host'].$port.$path;
    }

    private function assertAllowedImageContentType(string $contentType): void
    {
        $main = strtolower(trim(Str::before($contentType, ';')));
        $allowed = ['image/jpeg', 'image/jpg', 'image/png'];

        if (! in_array($main, $allowed, true)) {
            throw ImageFetchFailedException::because('URL did not return a JPEG or PNG image.');
        }
    }

    private function assertImageMagicBytes(string $body): void
    {
        if (strlen($body) < 8) {
            throw ImageFetchFailedException::because('Image file is too small or corrupted.');
        }

        $isJpeg = str_starts_with($body, "\xFF\xD8\xFF");
        $isPng = str_starts_with($body, "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A");

        if (! $isJpeg && ! $isPng) {
            throw ImageFetchFailedException::because('File is not a valid JPEG or PNG image.');
        }
    }
}

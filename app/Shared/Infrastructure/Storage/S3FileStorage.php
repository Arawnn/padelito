<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Storage;

use App\Shared\Domain\Contracts\FileStorageInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use App\Shared\Infrastructure\Exceptions\InfrastructureException;
use InvalidArgumentException;

final readonly class S3FileStorage implements FileStorageInterface
{
    public function __construct(
        private FilesystemAdapter $disk,
    ) {}

    public function upload(string $path, mixed $file): string
    {
        $normalizedPath = ltrim($path, '/');

        if ($file instanceof UploadedFile || $file instanceof File) {
            $directory = dirname($normalizedPath);
            $directory = $directory === '.' || $directory === '' ? '' : $directory;
            $filename = basename($normalizedPath);

            $storedPath = $this->disk->putFileAs($directory, $file, $filename, ['visibility' => Filesystem::VISIBILITY_PUBLIC]);
            if ($storedPath === false) {
                throw $this->uploadFailedException('putFileAs');
            }
        } elseif (is_string($file)) {
            if ($file === '') {
                throw new InvalidArgumentException('File contents must not be empty.');
            }
            if (! $this->disk->put($normalizedPath, $file, ['visibility' => Filesystem::VISIBILITY_PUBLIC])) {
                throw $this->uploadFailedException('put');
            }
            $storedPath = $normalizedPath;
        } else {
            throw new InvalidArgumentException(sprintf('Unsupported file type: %s.', get_debug_type($file)));
        }

        return $this->disk->url($storedPath);
    }

    public function delete(string $url): void
    {
        $key = $this->objectKeyFromPublicUrl($url);
        if ($key === null || $key === '') {
            return;
        }

        $this->disk->delete($key);
    }

    private function objectKeyFromPublicUrl(string $url): ?string
    {
        $config = $this->disk->getConfig();
        $baseUrl = isset($config['url']) ? rtrim((string) $config['url'], '/') : '';

        if ($baseUrl !== '' && str_starts_with($url, $baseUrl)) {
            return rawurldecode(ltrim(substr($url, strlen($baseUrl)), '/'));
        }

        $parsed = parse_url($url);
        if ($parsed === false || ! isset($parsed['path'])) {
            return null;
        }

        $path = rawurldecode(ltrim($parsed['path'], '/'));
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        $bucket = isset($config['bucket']) ? (string) $config['bucket'] : '';

        if ($bucket !== '' && str_starts_with($path, $bucket.'/')) {
            return substr($path, strlen($bucket) + 1);
        }

        return $path;
    }

    private function uploadFailedException(string $operation): InfrastructureException
    {
        $driver = (string) ($this->disk->getConfig()['driver'] ?? 'unknown');

        return InfrastructureException::storageFailed($operation, $driver);
    }
}

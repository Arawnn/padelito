<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Services;

use App\Features\Player\Application\Contracts\AvatarProvisionerInterface;
use App\Features\Player\Application\Dto\AvatarInput;
use App\Shared\Domain\Contracts\FileStorageInterface;
use App\Shared\Domain\Contracts\ImageFetchInterface;
use Illuminate\Http\File;
use Illuminate\Support\Str;

final readonly class DefaultAvatarProvisioner implements AvatarProvisionerInterface
{
    private const UI_AVATARS_API = 'https://ui-avatars.com/api/';

    public function __construct(
        private FileStorageInterface $fileStorage,
        private ImageFetchInterface $imageFetch,
    ) {}

    public function provision(
        string $userId,
        ?string $displayName,
        ?AvatarInput $avatar,
    ): ?string {
        if ($avatar?->hasUploadedFile()) {
            $ext = strtolower((string) $avatar->uploadedFileExtension);
            $ext = match ($ext) {
                'jpg', 'jpeg' => 'jpg',
                'png' => 'png',
                default => 'jpg',
            };

            $path = 'avatars/'.$userId.'/'.(string) Str::uuid().'.'.$ext;

            return $this->fileStorage->upload($path, new File($avatar->uploadedFilePath));
        }

        if ($avatar?->hasRemoteUrl()) {
            return $this->fetchAndStoreFromUrl($avatar->remoteUrl, $userId);
        }

        return $this->fetchAndStoreFromUrl(
            $this->placeholderUrlFromDisplayName($displayName),
            $userId,
        );
    }

    public function deleteByPublicUrl(string $publicUrl): void
    {
        $this->fileStorage->delete($publicUrl);
    }

    private function placeholderUrlFromDisplayName(?string $displayName): string
    {
        $name = trim($displayName ?? '');
        if ($name === '') {
            $name = '?';
        }

        $query = http_build_query([
            'name' => $name,
            'size' => 256,
            'background' => 'random',
            'bold' => 'true',
            'format' => 'png',
            'color' => 'fff',
            'rounded' => 'true',
        ], '', '&', PHP_QUERY_RFC3986);

        return self::UI_AVATARS_API.'?'.$query;
    }

    private function fetchAndStoreFromUrl(string $httpsUrl, string $userId): string
    {
        $bytes = $this->imageFetch->fetch($httpsUrl);

        $extension = str_starts_with($bytes, "\x89PNG") ? 'png' : 'jpg';
        $path = 'avatars/'.$userId.'/'.(string) Str::uuid().'.'.$extension;

        return $this->fileStorage->upload($path, $bytes);
    }
}

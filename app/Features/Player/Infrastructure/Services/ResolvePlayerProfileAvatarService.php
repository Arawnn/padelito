<?php

declare(strict_types=1);

namespace App\Features\Player\Infrastructure\Services;

use App\Features\Player\Infrastructure\Dto\CreatePlayerProfileAvatarInput;
use App\Shared\Application\Result;
use App\Shared\Domain\Contracts\FileStorageInterface;
use App\Shared\Domain\Contracts\ImageFetchInterface;
use App\Shared\Domain\Exceptions\ImageFetchFailedException;
use Illuminate\Support\Str;

final readonly class ResolvePlayerProfileAvatarService
{
    //TODO: hardcoded value for now, fallback should be behind an abstraction layer
    private const UI_AVATARS_API = 'https://ui-avatars.com/api/';

    public function __construct(
        private FileStorageInterface $fileStorage,
        private ImageFetchInterface $imageFetch,
    ) {}

    /**
     * @return Result<string> Public URL of the stored avatar (file, remote URL, or ui-avatars placeholder).
     */
    public function resolve(CreatePlayerProfileAvatarInput $input): Result
    {
        if ($input->avatarFile !== null) {
            $file = $input->avatarFile;
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $ext = match ($ext) {
                'jpg', 'jpeg' => 'jpg',
                'png' => 'png',
                default => 'jpg',
            };
            $path = 'avatars/'.$input->userId.'/'.(string) Str::uuid().'.'.$ext;

            return Result::ok($this->fileStorage->upload($path, $file));
        }

        if ($input->avatarAsHttpsUrlOrEmpty === '') {
            return $this->fetchAndStoreFromUrl(
                $this->placeholderUrlFromDisplayName($input->displayName),
                $input->userId,
            );
        }

        return $this->fetchAndStoreFromUrl($input->avatarAsHttpsUrlOrEmpty, $input->userId);
    }

    private function placeholderUrlFromDisplayName(string $displayName): string
    {
        $name = trim($displayName);
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

    private function fetchAndStoreFromUrl(string $httpsUrl, string $userId): Result
    {
        try {
            $bytes = $this->imageFetch->fetch($httpsUrl);
        } catch (ImageFetchFailedException $e) {
            return Result::fail($e);
        }

        $extension = str_starts_with($bytes, "\x89PNG") ? 'png' : 'jpg';
        $path = 'avatars/'.$userId.'/'.(string) Str::uuid().'.'.$extension;

        return Result::ok($this->fileStorage->upload($path, $bytes));
    }
}

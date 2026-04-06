<?php

declare(strict_types=1);

namespace App\Shared\Domain\Contracts;

interface FileStorageInterface
{
    /**
     * @param  mixed  $file  \Illuminate\Http\UploadedFile|\Illuminate\Http\File|string (binary)
     */
    public function upload(string $path, mixed $file): string;

    public function delete(string $url): void;
}

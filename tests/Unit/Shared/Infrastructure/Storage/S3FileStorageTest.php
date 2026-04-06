<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Infrastructure\Storage;

use App\Shared\Infrastructure\Storage\S3FileStorage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class S3FileStorageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_upload_returns_public_url_for_uploaded_file(): void
    {
        $file = UploadedFile::fake()->create('photo.jpg', 50, 'image/jpeg');

        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('putFileAs')
            ->once()
            ->with('avatars/user-1', Mockery::type(UploadedFile::class), 'abc.jpg', ['visibility' => Filesystem::VISIBILITY_PUBLIC])
            ->andReturn('avatars/user-1/abc.jpg');
        $disk->shouldReceive('url')
            ->once()
            ->with('avatars/user-1/abc.jpg')
            ->andReturn('https://cdn.example.test/avatars/user-1/abc.jpg');

        $storage = new S3FileStorage($disk);

        $this->assertSame(
            'https://cdn.example.test/avatars/user-1/abc.jpg',
            $storage->upload('avatars/user-1/abc.jpg', $file)
        );
    }

    public function test_upload_returns_public_url_for_binary_string(): void
    {
        $bytes = "\xFF\xD8\xFF\xE0";

        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('put')
            ->once()
            ->with('avatars/user-1/key.jpg', $bytes, ['visibility' => Filesystem::VISIBILITY_PUBLIC])
            ->andReturn(true);
        $disk->shouldReceive('url')
            ->once()
            ->with('avatars/user-1/key.jpg')
            ->andReturn('https://bucket.s3.amazonaws.com/avatars/user-1/key.jpg');

        $storage = new S3FileStorage($disk);

        $this->assertStringContainsString(
            'avatars/user-1/key.jpg',
            $storage->upload('avatars/user-1/key.jpg', $bytes)
        );
    }

    public function test_delete_removes_object_using_fake_disk_url(): void
    {
        Storage::fake('s3', ['url' => 'http://localhost']);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('s3');
        $disk->put('avatars/x.png', 'x', ['visibility' => 'public']);
        $url = $disk->url('avatars/x.png');

        $storage = new S3FileStorage($disk);
        $storage->delete($url);

        $this->assertFalse($disk->exists('avatars/x.png'));
    }
}

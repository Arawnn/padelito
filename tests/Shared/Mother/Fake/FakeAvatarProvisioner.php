<?php

declare(strict_types=1);

namespace Tests\Shared\Mother\Fake;

use App\Features\Player\Application\Contracts\AvatarProvisionerInterface;
use App\Features\Player\Application\Dto\AvatarInput;
use App\Features\Player\Domain\Exceptions\InvalidAvatarUrlException;

final class FakeAvatarProvisioner implements AvatarProvisionerInterface
{
    private bool $shouldFail = false;

    private bool $returnsNull = false;

    private string $fakeUrl = 'http://localhost/storage/avatars/test.jpg';

    public ?string $lastDeletedUrl = null;

    public ?string $lastProvisionedDisplayName = null;

    public static function thatSucceeds(string $url = 'http://localhost/storage/avatars/test.jpg'): self
    {
        $instance = new self;
        $instance->fakeUrl = $url;

        return $instance;
    }

    public static function thatFails(): self
    {
        $instance = new self;
        $instance->shouldFail = true;

        return $instance;
    }

    public static function thatReturnsNull(): self
    {
        $instance = new self;
        $instance->returnsNull = true;

        return $instance;
    }

    public function provision(string $userId, ?string $displayName, ?AvatarInput $avatar): ?string
    {
        $this->lastProvisionedDisplayName = $displayName;

        if ($this->returnsNull) {
            return null;
        }

        if ($this->shouldFail) {
            throw InvalidAvatarUrlException::fromViolations(['Avatar provisioning failed']);
        }

        return $this->fakeUrl;
    }

    public function deleteByPublicUrl(string $publicUrl): void
    {
        $this->lastDeletedUrl = $publicUrl;
    }
}

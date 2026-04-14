<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http\Exceptions;

use App\Shared\Domain\Exceptions\DomainExceptionInterface;
use Illuminate\Http\JsonResponse;

interface DomainExceptionRendererInterface
{
    public function handles(DomainExceptionInterface $e): bool;

    public function render(DomainExceptionInterface $e): JsonResponse;
}

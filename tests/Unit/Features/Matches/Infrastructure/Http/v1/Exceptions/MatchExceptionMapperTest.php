<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Matches\Infrastructure\Http\v1\Exceptions;

use App\Features\Matches\Domain\Exceptions\InvalidCourtNameException;
use App\Features\Matches\Domain\Exceptions\InvalidSetsDetailException;
use App\Features\Matches\Infrastructure\Http\v1\Exceptions\MatchExceptionMapper;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class MatchExceptionMapperTest extends TestCase
{
    public function test_it_maps_invalid_sets_detail_to_unprocessable_entity(): void
    {
        $exception = InvalidSetsDetailException::fromViolations(['Set 0 has an unrealistic padel score']);

        $response = (new MatchExceptionMapper)->render($exception);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame([
            'error' => [
                'code' => 'INVALID_SETS_DETAIL',
                'message' => 'The sets detail is invalid.',
                'details' => [
                    'violations' => ['Set 0 has an unrealistic padel score'],
                ],
            ],
        ], $response->getData(true));
    }

    public function test_it_maps_invalid_court_name_to_unprocessable_entity(): void
    {
        $exception = InvalidCourtNameException::fromViolations(['Court name must be at most 100 characters long']);

        $response = (new MatchExceptionMapper)->render($exception);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame([
            'error' => [
                'code' => 'INVALID_COURT_NAME',
                'message' => 'The court name is invalid.',
                'details' => [
                    'violations' => ['Court name must be at most 100 characters long'],
                ],
            ],
        ], $response->getData(true));
    }
}

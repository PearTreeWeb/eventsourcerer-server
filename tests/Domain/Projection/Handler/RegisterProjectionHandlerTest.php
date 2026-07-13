<?php

declare(strict_types=1);

namespace App\Tests\Domain\Projection\Handler;

use App\Domain\Projection\Command\RegisterProjection;
use App\Domain\Projection\Handler\RegisterProjectionHandler;
use App\Domain\Projection\Repository\ProjectionRepository;
use App\Tests\Double\Id;
use App\Tests\Double\ValueObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class RegisterProjectionHandlerTest extends TestCase
{
    public function testItRegistersAProjection(): void
    {
        $streamRepository = $this->createMock(ProjectionRepository::class);

        $streamRepository->expects($this->once())->method('create');

        $handler = new RegisterProjectionHandler($streamRepository, new MockClock());

        $handler(
            new RegisterProjection(
                Id::projectionId(),
                ValueObject::projectionName(),
                ValueObject::projectionEventProperties(),
                false,
                false,
                false,
                null,
                null,
            )
        );
    }
}

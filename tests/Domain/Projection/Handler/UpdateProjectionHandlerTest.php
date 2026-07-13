<?php

declare(strict_types=1);

namespace App\Tests\Domain\Projection\Handler;

use App\Domain\Projection\Command\UpdateProjection;
use App\Domain\Projection\Handler\UpdateProjectionHandler;
use App\Domain\Projection\Repository\ProjectionRepository;
use App\Tests\Double\Id;
use App\Tests\Double\ValueObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class UpdateProjectionHandlerTest extends TestCase
{
    public function testItRegistersAProjection(): void
    {
        $streamRepository = $this->createMock(ProjectionRepository::class);

        $streamRepository->expects($this->once())->method('update');

        (new UpdateProjectionHandler($streamRepository, new MockClock()))(
            new UpdateProjection(
                Id::projectionId(),
                ValueObject::projectionName(),
                ValueObject::projectionEventProperties(),
                false,
                false,
            )
        );
    }
}

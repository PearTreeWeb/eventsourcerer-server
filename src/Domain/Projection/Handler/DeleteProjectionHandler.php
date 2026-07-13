<?php

declare(strict_types=1);

namespace App\Domain\Projection\Handler;

use App\Domain\Projection\Command\DeleteProjection;
use App\Domain\Projection\Repository\ProjectionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteProjectionHandler
{
    public function __construct(private ProjectionRepository $projectionRepository) {}

    public function __invoke(DeleteProjection $command): void
    {
        $this->projectionRepository->delete(
            $this->projectionRepository->find($command->id)
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Projection\Handler;

use App\Domain\Projection\Query\GetProjectionMaxSequence;
use App\Domain\Projection\Repository\ProjectionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProjectionMaxSequenceHandler
{
    public function __construct(private ProjectionRepository $projectionRepository) {}

    public function __invoke(GetProjectionMaxSequence $query): int
    {
        return $this->projectionRepository->maxAllSequenceForProjection($query->id);
    }
}

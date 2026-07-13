<?php

declare(strict_types=1);

namespace App\Domain\Projection\Handler;

use App\Domain\Projection\Query\GetProjectionMasterStateByName;
use App\Domain\Projection\Repository\ProjectionRepository;
use App\Domain\Projection\Repository\ProjectionStateRepository;
use App\Entity\ProjectionState;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProjectionMasterStateByNameHandler
{
    public function __construct(
        private ProjectionRepository $projectionRepository,
        private ProjectionStateRepository $projectionStateRepository
    ) {}

    public function __invoke(GetProjectionMasterStateByName $query): ?ProjectionState
    {
        $projection = $this->projectionRepository->findByNameStrict($query->projectionName);

        return $this
            ->projectionStateRepository
            ->findMasterByProjectionId($projection->getId());
    }
}

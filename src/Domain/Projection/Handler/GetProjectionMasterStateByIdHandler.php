<?php

declare(strict_types=1);

namespace App\Domain\Projection\Handler;

use App\Domain\Projection\Query\GetProjectionMasterStateById;
use App\Domain\Projection\Repository\ProjectionStateRepository;
use App\Entity\ProjectionState;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProjectionMasterStateByIdHandler
{
    public function __construct(private ProjectionStateRepository $repository) {}

    public function __invoke(GetProjectionMasterStateById $query): ?ProjectionState
    {
        return $this->repository->findMasterByProjectionId($query->id);
    }
}

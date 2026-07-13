<?php

declare(strict_types=1);

namespace App\Domain\Application\Handler;

use App\Domain\Application\Query\GetApplicationCheckpoints;
use App\Domain\Application\Repository\ApplicationCheckpointRepository;
use App\Entity\ApplicationCheckpoint;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetApplicationCheckpointsHandler
{
    public function __construct(private ApplicationCheckpointRepository $repository) {}

    /**
     * @return iterable<ApplicationCheckpoint>
     */
    public function __invoke(GetApplicationCheckpoints $query): iterable
    {
        return $this->repository->byApplicationId($query->id);
    }
}

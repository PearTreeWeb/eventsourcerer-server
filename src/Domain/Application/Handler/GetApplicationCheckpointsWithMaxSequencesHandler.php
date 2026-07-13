<?php

namespace App\Domain\Application\Handler;

use App\Domain\Application\Query\GetApplicationCheckpointsWithMaxSequences;
use App\Domain\Application\Repository\ApplicationCheckpointRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetApplicationCheckpointsWithMaxSequencesHandler
{
    public function __construct(private ApplicationCheckpointRepository $repository) {}

    /**
     * @return iterable<array{streamId: string, maxSequence: int}>
     */
    public function __invoke(GetApplicationCheckpointsWithMaxSequences $query): iterable
    {
        return $this->repository->byApplicationIdWithMaxSequences($query->applicationId);
    }
}
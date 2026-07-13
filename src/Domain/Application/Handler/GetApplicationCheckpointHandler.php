<?php

declare(strict_types=1);

namespace App\Domain\Application\Handler;

use App\Domain\Application\Query\GetApplicationCheckpoint;
use App\Domain\Application\Repository\ApplicationCheckpointRepository;
use App\Entity\ApplicationCheckpoint;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetApplicationCheckpointHandler
{
    public function __construct(private ApplicationCheckpointRepository $applicationCheckpointRepository) {}

    public function __invoke(GetApplicationCheckpoint $query): ApplicationCheckpoint
    {
        return $this->applicationCheckpointRepository->findOrCreate(
            $query->applicationId,
            $query->streamId
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Application\Handler;

use App\Domain\Application\Command\ResetAllCheckpoints;
use App\Domain\Application\Repository\ApplicationCheckpointRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ResetAllCheckpointsHandler
{
    public function __construct(private ApplicationCheckpointRepository $repository)
    {
    }

    public function __invoke(ResetAllCheckpoints $command): void
    {
        $this->repository->resetAllForApplicationWithId($command->id);
    }
}

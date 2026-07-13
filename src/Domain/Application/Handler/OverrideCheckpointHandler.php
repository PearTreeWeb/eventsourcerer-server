<?php

declare(strict_types=1);

namespace App\Domain\Application\Handler;

use App\Domain\Application\Command\OverrideCheckpoint;
use App\Domain\Application\Repository\ApplicationCheckpointRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class OverrideCheckpointHandler
{
    public function __construct(private ApplicationCheckpointRepository $applicationCheckpointRepository) {}

    public function __invoke(OverrideCheckpoint $command): void
    {
        $checkpoint = $this->applicationCheckpointRepository->findOrCreate(
            $command->applicationId,
            $command->streamId
        )->setCheckpoint($command->checkpoint->toInt());

        $this->applicationCheckpointRepository->update($checkpoint);
    }
}

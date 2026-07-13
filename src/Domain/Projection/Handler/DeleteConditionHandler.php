<?php

declare(strict_types=1);

namespace App\Domain\Projection\Handler;

use App\Domain\Projection\Command\DeleteCondition;
use App\Domain\Projection\Repository\ProjectionMutationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteConditionHandler
{
    public function __construct(private ProjectionMutationRepository $projectionMutationRepository) {}

    public function __invoke(DeleteCondition $command): void
    {
        $mutationCondition = $this->projectionMutationRepository->findMutationCondition($command->conditionId);

        $this->projectionMutationRepository->removeCondition($mutationCondition);
    }
}

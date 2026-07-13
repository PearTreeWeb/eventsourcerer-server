<?php

namespace App\Domain\Projection\Handler;

use App\Domain\Projection\Command\DeleteConditionsGroup;
use App\Domain\Projection\Repository\ProjectionMutationRepository;
use App\Entity\MutationConditionsGroup;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteConditionsGroupHandler
{
    public function __construct(
        private ProjectionMutationRepository $repository,
    ) {}

    public function __invoke(DeleteConditionsGroup $command): void
    {
        $mutation = $this->repository->byIdStrict($command->projectionMutationId);

        $group = $mutation
            ->getConditionGroups()
            ->findFirst(static fn (int $key, MutationConditionsGroup $group) => $group->getId() === $command->conditionsGroupId);

        $this->repository->removeConditionGroup($group);
    }
}
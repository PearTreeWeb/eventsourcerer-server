<?php

declare(strict_types=1);

namespace App\Domain\Projection\Handler;

use App\Domain\Projection\Command\SetProjectionMutations;
use App\Domain\Projection\Model\ProjectionMutation;
use App\Domain\Projection\Repository\ProjectionRepository;
use App\Entity\ProjectionMutation as ProjectionMutationEntity;
use App\Entity\ProjectionProperty;
use Psr\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SetProjectionMutationsHandler
{
    public function __construct(
        private ProjectionRepository $projectionRepository,
        private ClockInterface $clock
    ) {}

    public function __invoke(SetProjectionMutations $command): void
    {
        $projection = $this->projectionRepository->find($command->id);

        $property = $projection
            ->getProperties()
            ->findFirst(
                static fn (int $i, ProjectionProperty $property) => $property
                    ->id()
                    ->equals($command->projectionEventPropertyId->toUuid())
            );

        $now = $this->clock->now();

        $command->stateMutations->each(
             function (ProjectionMutation $mutation) use ($projection, $property, $command, $now) {
                $property->addMutation(
                    ProjectionMutationEntity::create(
                        $command->projectionEventPropertyId,
                        $projection->getId(),
                        $command->eventId,
                        $mutation
                    )
                        ->setProjectionProperty($property)
                        ->setCreatedAt($now)
                );
            }
        );

        $this->projectionRepository->update($projection, $this->clock->now());
    }
}

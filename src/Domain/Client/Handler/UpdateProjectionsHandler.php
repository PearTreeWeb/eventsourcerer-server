<?php

declare(strict_types=1);

namespace App\Domain\Client\Handler;

use App\Domain\Client\Command\UpdateProjections;
use App\Domain\Projection\Command\RunProjectionMutation;
use App\Domain\Projection\Model\ProjectionStateType;
use App\Domain\Stream\Model\StreamEventId;
use App\Domain\Projection\Repository\ProjectionMutationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class UpdateProjectionsHandler
{
    public function __construct(
        private ProjectionMutationRepository $projectionMutationRepository,
        private MessageBusInterface $commandBus,
    ) {}

    public function __invoke(UpdateProjections $updateProjections): void
    {
        $eventPayloadProperties = $updateProjections->eventPayloadProperties;
        $projectionMutations = $this->projectionMutationRepository->findAllWithEventId($updateProjections->eventId);

        foreach ($projectionMutations as $projectionMutation) {
            $this->commandBus->dispatch(
                new RunProjectionMutation(
                    $projectionMutation,
                    $updateProjections->stream,
                    ProjectionStateType::Main,
                    $eventPayloadProperties,
                    $updateProjections->eventId,
                    $updateProjections->streamEvent->getAllSequence(),
                    StreamEventId::fromString($updateProjections->streamEvent->getId()->toRfc4122()),
                )
            );
        }
    }
}

<?php

namespace App\Domain\Projection\Handler;

use App\Domain\Client\Repository\StreamEventRepository;
use App\Domain\Event\Model\EventPayloadProperty;
use App\Domain\Event\Model\EventPropertyId;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Model\EventPropertyValue;
use App\Domain\Event\Repository\EventRepository;
use App\Domain\Stream\Model\StreamEventPayloadProperties;
use App\Domain\Stream\Model\StreamEventPayloadProperty;
use App\Domain\Projection\Command\RunProjectionMutation as RunProjectionMutationCommand;
use App\Domain\Projection\Exception\CannotRunMutation;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionMutationId;
use App\Domain\Projection\Model\ProjectionStateType;
use App\Domain\Projection\Repository\ProjectionMutationRepository;
use App\Domain\Projection\Repository\ProjectionRepository;
use App\Domain\Projection\Repository\ProjectionStateRepository;
use App\Domain\Projection\Service\RunProjectionMutation;
use App\Entity\ProjectionState;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use App\Repository\Postgres\PostgresProjectionMutationRepository;
use App\Repository\Postgres\PostgresProjectionStateRepository;
use App\Repository\Postgres\PostgresStreamEventRepository;
use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RunProjectionMutationHandler
{
    public function __construct(
        private RunProjectionMutation $runProjectionMutation,
        #[Autowire(service: PostgresProjectionStateRepository::class)]
        private ProjectionStateRepository $projectionStateRepository,
        #[Autowire(service: PostgresProjectionMutationRepository::class)]
        private ProjectionMutationRepository $projectionMutationRepository,
        #[Autowire(service: PostgresStreamEventRepository::class)]
        private StreamEventRepository $streamEventRepository,
        private ProjectionRepository $projectionRepository,
        private EventRepository $eventRepository,
        private ClockInterface $clock,
    ) {}

    public function __invoke(RunProjectionMutationCommand $command): void
    {
        $projectionMutation = $command->projectionMutation;
        $projectionId       = $projectionMutation->getProjectionId();

        $projection = $this->projectionRepository->find($projectionId);
        $startDate = $projection->getStartDate();
        $endDate   = $projection->getEndDate();

        if (null !== $command->streamEventId && (null !== $startDate || null !== $endDate)) {
            $streamEvent = $this->streamEventRepository->find($command->streamEventId);
            if (null !== $streamEvent) {
                $recordedAt = $streamEvent->createdAt();
                if (null !== $startDate && $recordedAt < $startDate) {
                    return;
                }
                if (null !== $endDate && $recordedAt > $endDate) {
                    return;
                }
            }
        }

        $this->guardCanRunMutation($command->type, $projectionId);

        // Fetch raw mutation row (includes projection_property_name, projection_property_type, type)

        // Fetch StreamEventPayloadProperties if streamEventId is available
        if (null === $command->streamEventId) {
            return;
        }

        $payloadProperties = $this->streamEventRepository->payloadPropertiesFor($command->streamEventId);

        // Fetch all mutations for this event and projection (there might be more than one)
        $mutations = $this->projectionMutationRepository->findAllForEvent($command->eventId, $projectionId);

        if (empty($mutations)) {
            return;
        }

        // Add metadata property to payloadProperties
        $event = $this->eventRepository->find($command->eventId);
        $metadataValue = EventPropertyValue::fromString(
            json_encode([
                'event'      => $event->getName(),
                'recordedAt' => $event->getCreatedAt()->getTimestamp(),
                'stream'     => $command->stream->getId()->toString(),
                'version'    => $event->getVersion(),
            ], JSON_THROW_ON_ERROR)
        );

        $payloadProperties = StreamEventPayloadProperties::fromArray(
            array_merge(
                $payloadProperties->toArray(),
                [
                    new StreamEventPayloadProperty(
                        EventPropertyId::metadata(),
                        new EventPayloadProperty(EventPropertyName::metadata(), $metadataValue),
                    ),
                ]
            )
        );

        // Load or create master projection state
        $masterState = $this->projectionStateRepository->findMasterByProjectionId($projectionId)
            ?? ProjectionState::create($projectionId, ProjectionStateType::Main, $this->clock->now());

        // Load or create stream-partitioned projection state
        $streamId        = StreamId::fromString($command->stream->getId()->toString());
        $partitionedState = $this->projectionStateRepository->findByStreamAndProjectionId($streamId, ProjectionStateType::Main, $projectionId)
            ?? ProjectionState::create($projectionId, ProjectionStateType::Main, $this->clock->now(), $streamId);

        $stateChanged = false;

        foreach ($mutations as $mutation) {
            $mutationId = ProjectionMutationId::fromString($mutation['id']);
            $mutationConditionGroups = $this->projectionMutationRepository
                ->conditionGroupsForProjectionMutationWithId($mutationId);

            $updatedMasterState = $this->runProjectionMutation->with(
                $payloadProperties,
                $mutation,
                $mutationConditionGroups,
                $masterState->getCurrentState(),
            );

            $updatedPartitionedState = $this->runProjectionMutation->with(
                $payloadProperties,
                $mutation,
                $mutationConditionGroups,
                $partitionedState->getCurrentState(),
            );

            if (null !== $updatedMasterState) {
                $masterState->setCurrentState($updatedMasterState);
                $stateChanged = true;
            }

            if (null !== $updatedPartitionedState) {
                $partitionedState->setCurrentState($updatedPartitionedState);
                $stateChanged = true;
            }
        }

        if ($stateChanged) {
            $this->projectionStateRepository->update($masterState);
            $this->projectionStateRepository->update($partitionedState);

            $projection = $this->projectionRepository->find($projectionId);
            $projection->setLastAllSequenceCheckpointProcessed($command->allSequence);
            $this->projectionRepository->update($projection, $this->clock->now());
        }
    }

    private function guardCanRunMutation(ProjectionStateType $type, ProjectionId $projectionId): void
    {
        if (
            ProjectionStateType::Reset !== $type &&
            $this->projectionStateRepository->findResetByProjectionId($projectionId)
        ) {
            throw CannotRunMutation::becauseAResetIsHappeningCurrently($projectionId);
        }
    }
}

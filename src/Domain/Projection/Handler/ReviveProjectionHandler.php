<?php

namespace App\Domain\Projection\Handler;

use App\Domain\Client\Repository\StreamEventRepository;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventPayloadProperty;
use App\Domain\Event\Model\EventPropertyId;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Model\EventPropertyValue;
use App\Domain\Projection\Command\ReviveProjection;
use App\Domain\Projection\Model\ProjectionCondition;
use App\Domain\Projection\Model\ProjectionMutationId;
use App\Domain\Projection\Model\ProjectionStateType;
use App\Domain\Projection\Repository\ProjectionMutationRepository;
use App\Domain\Projection\Repository\ProjectionRepository;
use App\Domain\Projection\Repository\ProjectionStateRepository;
use App\Domain\Projection\Service\RunProjectionMutation;
use App\Domain\Stream\Model\StreamEventPayloadProperties;
use App\Domain\Stream\Model\StreamEventPayloadProperty;
use App\Entity\ProjectionState;
use App\Repository\Postgres\PostgresProjectionMutationRepository;
use App\Repository\Postgres\PostgresProjectionRepository;
use App\Repository\Postgres\PostgresProjectionStateRepository;
use App\Repository\Postgres\PostgresStreamEventRepository;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ReviveProjectionHandler
{
    public function __construct(
        #[Autowire(service: PostgresProjectionRepository::class)]
        private ProjectionRepository $projectionRepository,
        #[Autowire(service: PostgresProjectionMutationRepository::class)]
        private ProjectionMutationRepository $projectionMutationRepository,
        private RunProjectionMutation $runProjectionMutation,
        #[Autowire(service: PostgresStreamEventRepository::class)]
        private StreamEventRepository $streamEventRepository,
        #[Autowire(service: PostgresProjectionStateRepository::class)]
        private ProjectionStateRepository $projectionStateRepository,
        private ClockInterface $clock,
    ) {}

    private const int PERSIST_BATCH_SIZE = 100;

    public function __invoke(ReviveProjection $command): void
    {
        $projection = $this->projectionRepository->find($command->id);

        $projection->setCondition(ProjectionCondition::Running->value);
        $this->projectionRepository->update($projection, $this->clock->now());

        /** @var array<string, ProjectionState> $pendingStates */
        $pendingStates  = [];
        $eventCount     = 0;
        $lastAllSequence = null;

        $lastProcessedAllSequence = Checkpoint::fromInt($projection->getLastAllSequenceCheckpointProcessed());

        foreach ($this->streamEventRepository->allIterableRaw() as $streamEvent) {
            $allSequence = (int) $streamEvent['all_sequence'];

            if ($allSequence <= $lastProcessedAllSequence->toInt()) {
                continue;
            }

            $eventId   = EventId::fromString($streamEvent['event_id']);
            $mutations = $this->projectionMutationRepository->findAllForEvent($eventId, $command->id);

            $propItems = array_map(
                fn (array $prop) => new StreamEventPayloadProperty(
                    EventPropertyId::fromString($prop['event_property_id']),
                    new EventPayloadProperty(
                        EventPropertyName::fromString($prop['prop_name']),
                        EventPropertyValue::fromString($prop['prop_value']),
                    ),
                ),
                $streamEvent['properties'],
            );

            if (!empty($streamEvent['properties'])) {
                $first = $streamEvent['properties'][0];
                $propItems[] = new StreamEventPayloadProperty(
                    EventPropertyId::metadata(),
                    new EventPayloadProperty(
                        EventPropertyName::metadata(),
                        EventPropertyValue::fromString(
                            json_encode([
                                'event'      => $first['event_name'],
                                'recordedAt' => $first['recorded_at'],
                                'stream'     => $first['stream_id'],
                                'version'    => $first['event_version'],
                            ], JSON_THROW_ON_ERROR)
                        ),
                    ),
                );
            }

            $payloadProperties = StreamEventPayloadProperties::fromArray($propItems);

            foreach ($mutations as $mutation) {
                $mutationConditionGroups = $this
                    ->projectionMutationRepository
                    ->conditionGroupsForProjectionMutationWithId(ProjectionMutationId::fromString($mutation['id']));

                $masterKey   = 'master';
                $masterState = $pendingStates[$masterKey]
                    ?? $this->projectionStateRepository->findMasterByProjectionId($command->id)
                    ?? ProjectionState::create($command->id, ProjectionStateType::Main, $this->clock->now());

                $streamId        = StreamId::fromString($streamEvent['stream_id']);
                $partitionedKey  = 'stream_' . $streamId->toString();
                $partitionedState = $pendingStates[$partitionedKey]
                    ?? $this->projectionStateRepository->findByStreamAndProjectionId($streamId, ProjectionStateType::Main, $command->id)
                    ?? ProjectionState::create($command->id, ProjectionStateType::Main, $this->clock->now(), $streamId);

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
                    $pendingStates[$masterKey] = $masterState;
                }

                if (null !== $updatedPartitionedState) {
                    $partitionedState->setCurrentState($updatedPartitionedState);
                    $pendingStates[$partitionedKey] = $partitionedState;
                }

                if (null !== $updatedMasterState || null !== $updatedPartitionedState) {
                    $lastAllSequence = $allSequence;
                }
            }

            $eventCount++;

            if ($eventCount % self::PERSIST_BATCH_SIZE === 0) {
                foreach ($pendingStates as $pendingState) {
                    $this->projectionStateRepository->update($pendingState);
                }

                if (null !== $lastAllSequence) {
                    $projection->setLastAllSequenceCheckpointProcessed($lastAllSequence);
                    $this->projectionRepository->update($projection, $this->clock->now());
                }

                $pendingStates = [];
            }
        }

        foreach ($pendingStates as $pendingState) {
            $this->projectionStateRepository->update($pendingState);
        }

        if (null !== $lastAllSequence) {
            $projection->setLastAllSequenceCheckpointProcessed($lastAllSequence);
        }

        $this->projectionRepository->update($projection, $this->clock->now());
    }
}

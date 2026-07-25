<?php

declare(strict_types=1);

namespace App\Domain\Projection\Service;

use App\Domain\Event\Model\EventPayloadProperty;
use App\Domain\Event\Model\EventPropertyId;
use App\Domain\Projection\Model\MutationType;
use App\Domain\Projection\Model\ProjectionMutationConditionGroups;
use App\Domain\Stream\Model\StreamEventPayloadProperties;
use App\Repository\Mutations;
use Psr\Log\LoggerInterface;

final readonly class RunProjectionMutation
{
    public const string PARTITIONED_RESULTS = 'partitionedResults';

    public function __construct(
        private Mutations $mutations,
        private LoggerInterface $logger,
    ) {}

    /**
     * Applies a single projection mutation to the given current state and returns the updated state.
     *
     * @param StreamEventPayloadProperties      $payloadProperties     Payload properties for this stream event
     * @param array<string, mixed>              $mutation              Raw mutation row (id, event_property_id, projection_property_id, type, projection_property_name, projection_property_type)
     * @param ProjectionMutationConditionGroups $mutationConditionGroups Condition groups for this mutation
     * @param array<string, mixed>              $currentState          Current projection state array
     *
     * @return array<string, mixed>|null Updated state array, or null if no change should be applied
     */
    public function with(
        StreamEventPayloadProperties $payloadProperties,
        array $mutation,
        ProjectionMutationConditionGroups $mutationConditionGroups,
        array $currentState,
    ): ?array {
        try {
            $eventPropertyId        = EventPropertyId::fromString($mutation['event_property_id']);
            $projectionPropertyName = $mutation['projection_property_name'];
            $projectionPropertyType = $mutation['projection_property_type'];
            $mutationType           = MutationType::fromString($mutation['type']);

            // Find the matching payload property by event_property_id
            $matchedPayloadProperty = $payloadProperties->items()->first(
                static fn ($p) => $p->id->toString() === $eventPropertyId->toString()
            );

            if (null === $matchedPayloadProperty) {
                return null;
            }

            /** @var EventPayloadProperty $eventPayloadProperty */
            $eventPayloadProperty = $matchedPayloadProperty->eventPayloadProperty;

            $currentValue = $currentState[$projectionPropertyName] ?? null;

            // Check conditions
            $conditionsSatisfied = ConditionsChecker::isSatisfiedWith(
                $mutationConditionGroups,
                new $projectionPropertyType(),
                $eventPayloadProperty,
                $payloadProperties,
            );

            if (false === $conditionsSatisfied) {
                return null;
            }

            $newValue = $this->mutations
                ->byType($mutationType)
                ?->mutate($eventPayloadProperty->value, $currentValue);

            if (null === $newValue) {
                return null;
            }

            $currentState[$projectionPropertyName] = $newValue;

            return $currentState;
        } catch (\Throwable $e) {
            $this->logger->warning($e->getMessage());

            return null;
        }
    }
}

<?php

namespace App\Domain\Projection\Repository;

use App\Domain\Event\Model\EventId;
use App\Domain\Projection\Model\ProjectionEventPropertyId;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionMutationConditionGroups;
use App\Domain\Projection\Model\ProjectionMutationId;
use App\Domain\Stream\Model\StreamEventId;
use App\Entity\MutationCondition;
use App\Entity\MutationConditionsGroup;
use App\Entity\ProjectionMutation;

interface ProjectionMutationRepository
{
    public function byIdStrict(ProjectionMutationId $id): ProjectionMutation;

    /**
     * @return ProjectionMutation[]
     */
    public function findAllWithEventId(EventId $id): array;

    /**
     * @return ProjectionMutation[]
     */
    public function findAllWithProjectionId(ProjectionId $projectionId): array;

    /**
     * @return ProjectionMutation[]
     */
    public function findAllWithEventIdAndEventPropertyId(
        EventId $id,
        ProjectionEventPropertyId $eventPropertyId
    ): array;

    /**
     * @return ProjectionMutation[]
     */
    public function findAllWithEventIdAndProjectionId(EventId $id, ProjectionId $projectionId): array;

    public function findMutationCondition(int $mutationConditionId): MutationCondition;

    public function removeConditionGroup(MutationConditionsGroup $mutationConditionsGroup): void;

    public function removeCondition(MutationCondition $condition): void;

    /**
     * @return array<array{id: string, event_property_id: string, projection_property_id: string}>
     */
    public function findAllForEvent(EventId $eventId, ProjectionId $projectionId): array;

    public function conditionGroupsForProjectionMutationWithId(
        ProjectionMutationId $id
    ): ProjectionMutationConditionGroups;
}

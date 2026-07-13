<?php

namespace App\Domain\Projection\Repository;

use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionStateType;
use App\Entity\ProjectionState;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

interface ProjectionStateRepository
{
    public function add(ProjectionState $projectionState): void;

    public function update(ProjectionState $projectionState): void;

    public function findByStreamAndProjectionId(
        StreamId $streamId,
        ProjectionStateType $type,
        ProjectionId $projectionId
    ): ?ProjectionState;

    public function findMasterByProjectionId(ProjectionId $projectionId): ?ProjectionState;

    public function findResetByProjectionId(ProjectionId $projectionId): ?ProjectionState;

    /**
     * @return array<ProjectionState>
     */
    public function findAllCurrentWithProjectionId(ProjectionId $projectionId): array;

    public function delete(ProjectionState $projectionState): void;

    /**
     * @return array<ProjectionState>
     */
    public function findAllResetStatesWithProjectionId(ProjectionId $projectionId): array;
}

<?php

namespace App\Domain\Application\Repository;

use App\Entity\ApplicationCheckpoint;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

interface ApplicationCheckpointRepository
{
    public function update(ApplicationCheckpoint $applicationCheckpoint): ApplicationCheckpoint;

    public function create(ApplicationCheckpoint $applicationCheckpoint): ApplicationCheckpoint;

    public function findOrCreate(ApplicationId $id, StreamId $streamId): ApplicationCheckpoint;

    public function find(ApplicationId $id, StreamId $streamId): ?ApplicationCheckpoint;

    /**
     * @return iterable<ApplicationCheckpoint>
     */
    public function all(): iterable;

    /**
     * @return iterable<ApplicationCheckpoint>
     */
    public function byApplicationId(ApplicationId $id): iterable;

    /**
     * @return iterable<array{streamId: string, maxSequence: int, checkpoint: int}>
     */
    public function byApplicationIdWithMaxSequences(ApplicationId $id): iterable;

    public function resetAllForApplicationWithId(ApplicationId $id): void;
}

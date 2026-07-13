<?php

declare(strict_types=1);

namespace App\Tests\Double\Repository;

use App\Domain\Application\Repository\ApplicationCheckpointRepository as ApplicationCheckpointRepositoryInterface;
use App\Entity\ApplicationCheckpoint;
use App\Tests\Double\ValueObject;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

final class ApplicationCheckpointRepository implements ApplicationCheckpointRepositoryInterface
{
    /**
     * @param ApplicationCheckpoint[] $checkpoints
     */
    public function __construct(
        private array $checkpoints
    ) {}

    public function update(ApplicationCheckpoint $applicationCheckpoint): ApplicationCheckpoint
    {
        $this->checkpoints[$applicationCheckpoint->applicationId()][$applicationCheckpoint->streamId()]
            = $applicationCheckpoint;

        return $applicationCheckpoint;
    }

    public function create(ApplicationCheckpoint $applicationCheckpoint): ApplicationCheckpoint
    {
    }

    public function findOrCreate(ApplicationId $id, StreamId $streamId): ApplicationCheckpoint
    {
        return $this->checkpoints[$id->toString()][$streamId->toString()] ?? ApplicationCheckpoint::create(
            $id,
            $streamId,
            new \DateTimeImmutable()
        );
    }

    public function all(): iterable
    {
        return [];
    }

    public function byApplicationId(ApplicationId $id): iterable
    {
        return [];
    }

    public function find(ApplicationId $id, StreamId $streamId): ?ApplicationCheckpoint
    {
        return ApplicationCheckpoint::create(
            $id,
            $streamId,
            ValueObject::createdAt()
        );
    }

    public function resetAllForApplicationWithId(ApplicationId $id): void
    {
    }

    public function byApplicationIdWithMaxSequences(ApplicationId $id): iterable
    {
        return [];
    }
}

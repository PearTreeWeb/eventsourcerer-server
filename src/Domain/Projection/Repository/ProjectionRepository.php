<?php

namespace App\Domain\Projection\Repository;

use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionName;
use App\Entity\Projection;

interface ProjectionRepository
{
    public function update(Projection $projection, \DateTimeImmutable $now): Projection;

    public function create(Projection $projection): Projection;

    public function delete(Projection $projection): void;

    public function find(ProjectionId $id): Projection;

    public function findByNameStrict(ProjectionName $name): Projection;

    public function findByName(ProjectionName $name): ?Projection;

    /**
     * @return Projection[]
     */
    public function all(): array;

    public function doesNotExist(ProjectionName $name): bool;

    public function updateConditionGroups(Projection $projection, \DateTimeImmutable $now): void;

    public function maxAllSequenceForProjection(ProjectionId $id): int;

    public function resetProjection(Projection $projection, \DateTimeImmutable $resetAt): Projection;
}

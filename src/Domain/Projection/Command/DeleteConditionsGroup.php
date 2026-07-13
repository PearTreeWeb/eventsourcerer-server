<?php

namespace App\Domain\Projection\Command;

use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionMutationId;
use App\Domain\Projection\Model\ProjectionPropertyId;

final readonly class DeleteConditionsGroup
{
    public function __construct(
        public int $conditionsGroupId,
        public ProjectionId $projectionId,
        public ProjectionPropertyId $projectionPropertyId,
        public ProjectionMutationId $projectionMutationId
    ) {}
}
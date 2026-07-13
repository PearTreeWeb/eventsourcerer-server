<?php

declare(strict_types=1);

namespace App\Domain\Projection\Command;

use App\Domain\Event\Model\EventId;
use App\Domain\Projection\Model\ProjectionEventPropertyId;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionMutations;

final readonly class SetProjectionMutations
{
    public function __construct(
        public ProjectionId $id,
        public EventId $eventId,
        public ProjectionEventPropertyId $projectionEventPropertyId,
        public ProjectionMutations $stateMutations
    ) {}
}

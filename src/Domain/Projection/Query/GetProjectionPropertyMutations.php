<?php

declare(strict_types=1);

namespace App\Domain\Projection\Query;

use App\Domain\Common\Model\Query;
use App\Domain\Event\Model\EventId;
use App\Domain\Projection\Model\ProjectionEventPropertyId;
use App\Entity\ProjectionMutation;

/**
 * @implements Query<ProjectionMutation[]>
 */
final readonly class GetProjectionPropertyMutations implements Query
{
    public function __construct(
        public EventId $eventId,
        public ProjectionEventPropertyId $id
    ) {}
}

<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

use App\Domain\Common\HasKey;
use App\Domain\Event\Model\EventPropertyId;

final readonly class ProjectionMutation implements HasKey
{
    public function __construct(
        public ProjectionMutationId $id,
        public EventPropertyId $eventPropertyId,
        public CanMutate $mutationType,
        public MutationConditionGroups $mutationConditionGroups,
    ) {}

    public function key(): string
    {
        return $this->eventPropertyId->toString();
    }
}

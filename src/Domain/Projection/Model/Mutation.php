<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Model\EventVersion;

final readonly class Mutation
{
    public function __construct(
        public EventName $eventName,
        public EventVersion $eventVersion,
        public CanMutate $mutationType,
        public EventPropertyName $eventPropertyName,
        public ProjectionPropertyName $projectionPropertyName
    ) {}
}

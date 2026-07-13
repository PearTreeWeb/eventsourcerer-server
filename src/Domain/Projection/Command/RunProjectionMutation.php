<?php

namespace App\Domain\Projection\Command;

use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventPayloadProperties;
use App\Domain\Projection\Model\ProjectionStateType;
use App\Domain\Stream\Model\StreamEventId;
use App\Entity\ProjectionMutation;
use App\Entity\Stream;

final readonly class RunProjectionMutation
{
    public function __construct(
        public ProjectionMutation $projectionMutation,
        public Stream $stream,
        public ProjectionStateType $type,
        public EventPayloadProperties $eventPayloadProperties,
        public EventId $eventId,
        public int $allSequence = 0,
        public ?StreamEventId $streamEventId = null,
    ) {}
}
<?php

declare(strict_types=1);

namespace App\Domain\Client\Command;

use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventPayloadProperties;
use App\Entity\Stream;
use App\Entity\StreamEvent;

final readonly class UpdateProjections
{
    public function __construct(
        public Stream $stream,
        public EventId $eventId,
        public EventPayloadProperties $eventPayloadProperties,
        public StreamEvent $streamEvent,
    ) {}
}

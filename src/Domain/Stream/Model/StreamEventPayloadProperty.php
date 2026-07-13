<?php

namespace App\Domain\Stream\Model;

use App\Domain\Common\HasKey;
use App\Domain\Event\Model\EventPayloadProperty;
use App\Domain\Event\Model\EventPropertyId;

final readonly class StreamEventPayloadProperty implements HasKey
{
    public function __construct(
        public EventPropertyId $id,
        public EventPayloadProperty $eventPayloadProperty,
    ) {}

    public function key(): string
    {
        return $this->id->toString();
    }
}
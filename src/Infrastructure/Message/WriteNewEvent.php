<?php

declare(strict_types=1);

namespace App\Infrastructure\Message;

use PearTreeWeb\EventSourcerer\Common\Model\EventName;
use PearTreeWeb\EventSourcerer\Common\Model\EventVersion;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

final readonly class WriteNewEvent implements Message
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public StreamId $streamId,
        public EventName $eventName,
        public EventVersion $eventVersion,
        public array $payload,
        public array $metadata,
        public ?int $expectedVersion = null,
    ) {}
}

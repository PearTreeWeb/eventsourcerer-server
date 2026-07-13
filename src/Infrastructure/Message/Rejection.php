<?php

declare(strict_types=1);

namespace App\Infrastructure\Message;

use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

final readonly class Rejection implements Message
{
    public function __construct(private NewEvent $originalEvent) {}

    public function streamId(): StreamId
    {
        return $this->originalEvent->streamId;
    }

    public function checkpoint(): Checkpoint
    {
        return $this->originalEvent->checkpoint;
    }

    public function json(): string
    {
        return $this->originalEvent->json;
    }

    public function event(): NewEvent
    {
        return $this->originalEvent;
    }
}

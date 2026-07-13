<?php

namespace App\Domain\Common\Command;

use App\Entity\StreamEvent;

final readonly class BroadcastEvent
{
    public function __construct(public StreamEvent $streamEvent) {}
}
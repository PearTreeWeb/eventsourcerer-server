<?php

namespace App\Domain\Client\Service;

use App\Entity\Stream;
use App\Entity\StreamEvent;

interface RecordEvent
{
    public function record(StreamEvent $streamEvent, Stream $stream): void;
}

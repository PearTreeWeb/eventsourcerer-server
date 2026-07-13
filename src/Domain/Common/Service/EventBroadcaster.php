<?php

namespace App\Domain\Common\Service;

use App\Infrastructure\Exception\CouldNotBroadcastEvent;
use Stringable;

interface EventBroadcaster
{
    public function broadcast(string $message): void;

    /**
     * @throws CouldNotBroadcastEvent
     */
    public function broadcastSync(string|Stringable $message): void;
}

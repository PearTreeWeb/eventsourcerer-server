<?php

namespace App\Domain\Common\Handler;

use App\Domain\Common\Command\BroadcastEvent;
use App\Domain\Common\Service\EventBroadcaster;
use App\Infrastructure\FormatEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class BroadcastEventHandler
{
    public function __construct(private EventBroadcaster $eventBroadcaster) {}

    public function __invoke(BroadcastEvent $command): void
    {
        $this->eventBroadcaster->broadcastSync(FormatEvent::toJson($command->streamEvent));
    }
}

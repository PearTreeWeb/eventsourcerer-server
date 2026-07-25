<?php

declare(strict_types=1);

namespace App\Tests\Domain\Common\Handler;

use App\Domain\Common\Command\BroadcastEvent;
use App\Domain\Common\Handler\BroadcastEventHandler;
use App\Domain\Common\Service\EventBroadcaster;
use App\Infrastructure\FormatEvent;
use App\Tests\Double\Entity;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class BroadcastEventHandlerTest extends TestCase
{
    private BroadcastEventHandler $handler;
    private MockObject&EventBroadcaster $eventBroadcaster;

    protected function setUp(): void
    {
        $this->eventBroadcaster = $this->createMock(EventBroadcaster::class);
        $this->handler = new BroadcastEventHandler($this->eventBroadcaster);
    }

    #[Test]
    public function itBroadcastsFormattedEvent(): void
    {
        $command = new BroadcastEvent(Entity::streamEvent());

        $this
            ->eventBroadcaster
            ->expects($this->once())
            ->method('broadcastSync')
            ->with(FormatEvent::toJson(Entity::streamEvent()));

        $this->handler->__invoke($command);
    }

    #[Test]
    public function itCanBeConstructed(): void
    {
        $handler = new BroadcastEventHandler($this->eventBroadcaster);

        $this->assertInstanceOf(BroadcastEventHandler::class, $handler);
    }
}

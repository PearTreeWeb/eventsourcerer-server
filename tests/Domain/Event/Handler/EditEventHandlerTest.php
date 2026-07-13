<?php

declare(strict_types=1);

namespace App\Tests\Domain\Event\Handler;

use App\Domain\Event\Command\EditEvent;
use App\Domain\Event\Handler\EditEventHandler;
use App\Domain\Event\Repository\EventRepository;
use App\Infrastructure\EventSourcerer\Service\GenerateUuid;
use App\Tests\Double\Entity;
use App\Tests\Double\Id;
use App\Tests\Double\ValueObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\NativeClock;
use Symfony\Component\Uid\Factory\UuidFactory;

final class EditEventHandlerTest extends TestCase
{
    public function testItEditsEvent(): void
    {
        $eventRepository = $this->createMock(EventRepository::class);

        $eventRepository
            ->method('findStrict')
            ->willReturn(Entity::event());

        $handler = new EditEventHandler(
            $eventRepository,
            new NativeClock(),
            new GenerateUuid(new UuidFactory())
        );

        $eventRepository->expects($this->once())->method('update');

        $handler(
            new EditEvent(
                Id::eventId(),
                Id::eventId(),
                ValueObject::eventName(),
                ValueObject::eventProperties(),
                0
            )
        );
    }
}

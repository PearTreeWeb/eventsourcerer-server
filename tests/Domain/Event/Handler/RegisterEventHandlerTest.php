<?php

declare(strict_types=1);

namespace App\Tests\Domain\Event\Handler;

use App\Domain\Event\Command\RegisterEvent;
use App\Domain\Event\Handler\RegisterEventHandler;
use App\Domain\Event\Repository\EventRepository;
use App\Tests\Double\Id;
use App\Tests\Double\ValueObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\NativeClock;

final class RegisterEventHandlerTest extends TestCase
{
    public function testItRegistersEvent(): void
    {
        $eventRepository = $this->createMock(EventRepository::class);

        $handler = new RegisterEventHandler(
            $eventRepository,
            new NativeClock()
        );

        $eventRepository->expects($this->once())->method('create');

        $handler(
            new RegisterEvent(
                Id::eventId(),
                ValueObject::eventName(),
                ValueObject::eventProperties(),
                0
            )
        );
    }
}

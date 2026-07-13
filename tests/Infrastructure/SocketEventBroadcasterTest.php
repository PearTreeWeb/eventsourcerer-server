<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure;

use App\Infrastructure\SocketEventBroadcaster;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class SocketEventBroadcasterTest extends TestCase
{
    #[Test]
    public function itFormatsEventMessageCorrectly(): void
    {
        $message = 'test message';
        $formatted = SocketEventBroadcaster::formatEvent($message);

        $this->assertEquals('NEW_EVENT test message', $formatted);
    }

    #[Test]
    public function itFormatsStringableObjectMessage(): void
    {
        $message = new class {
            public function __toString(): string
            {
                return 'test object message';
            }
        };

        $formatted = SocketEventBroadcaster::formatEvent($message);

        $this->assertEquals('NEW_EVENT test object message', $formatted);
    }

    #[Test]
    public function itCanCreateInstanceWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $broadcaster = new SocketEventBroadcaster(
            '127.0.0.1',
            8080,
            $logger,
            '',
            '',
            '',
        );

        $this->assertInstanceOf(SocketEventBroadcaster::class, $broadcaster);
    }

    #[Test]
    public function itHasCorrectConstructorParameters(): void
    {
        $host = '192.168.1.100';
        $port = 9000;
        $logger = $this->createMock(LoggerInterface::class);

        $broadcaster = new SocketEventBroadcaster($host, $port, $logger, '', '', '');

        $this->assertInstanceOf(SocketEventBroadcaster::class, $broadcaster);
    }

    #[Test]
    public function itCanBeConstructedWithCorrectParameters(): void
    {
        $host = '192.168.1.100';
        $port = 9000;
        $logger = $this->createMock(LoggerInterface::class);

        $broadcaster = new SocketEventBroadcaster($host, $port, $logger, '', '', '');

        $this->assertInstanceOf(SocketEventBroadcaster::class, $broadcaster);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\SocketServer;

use App\Domain\Connection\Service\RecordConnection;
use App\Infrastructure\Message\Message;
use App\Infrastructure\MessageHandler\NewEvent;
use App\Infrastructure\MessageHandler\ProvideIdentity;
use App\Infrastructure\MessageHandler\Rejection;
use App\Infrastructure\MessageHandlers;
use App\Infrastructure\Repository\ActiveCatchupStreams;
use App\Infrastructure\Repository\RejectedMessages;
use App\Infrastructure\Repository\WorkerRepository;
use App\Infrastructure\SocketConnectionsPool;
use App\Infrastructure\SocketServer;
use App\Tests\Double\Repository\ApplicationCheckpointRepository;
use App\Tests\Double\SocketConnection;
use Evenement\EventEmitter;
use PearTreeWeb\EventSourcerer\Common\Model\MessageType;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class SocketServerTestCase extends TestCase
{
    protected SocketConnection $mockConnection;
    protected SocketConnection $mockConnectionConsumer;
    protected EventEmitter $eventEmitter;
    protected RejectedMessages $rejectedMessages;

    /** @var Message[] */
    private array $messagesReceivedByConsumer;

    protected function setUp(): void
    {
        $connectionsPool = SocketConnectionsPool::create(
            $this->createMock(RecordConnection::class)
        );

        $this->eventEmitter               = new EventEmitter();
        $this->mockConnection             = SocketConnection::create('123.123.123.123');
        $this->mockConnectionConsumer     = SocketConnection::create('124.124.124.124');
        $this->rejectedMessages           = new RejectedMessages(new ArrayAdapter());
        $this->messagesReceivedByConsumer = [];
        $logger                           = new ConsoleLogger(new ConsoleOutput());
        $checkpointRepository             = new ApplicationCheckpointRepository([]);

        $this->mockConnectionConsumer->on('data', function ($event) {
            if (str_contains($event, MessageType::NewEvent->value)) {
                $this->mockConnectionConsumer->sendMockAcknowledgementOfNewEvent($event);
                $this->messagesReceivedByConsumer[] = $event;
            }
        });

        $workerRepository = new WorkerRepository(new ArrayAdapter());

        SocketServer::start(
            $connectionsPool,
            $logger,
            MessageHandlers::create([
                new Rejection($this->rejectedMessages),
                new ProvideIdentity($workerRepository),
                new NewEvent($checkpointRepository, $this->rejectedMessages, $workerRepository, $logger),
            ]),
            $this->eventEmitter,
            new Clock(),
            new ActiveCatchupStreams(new ArrayAdapter()),
            $this->createMock(OutputInterface::class),
            $workerRepository,
        );
    }

    protected function fetchRejectedMessages(StreamId $streamId): iterable
    {
        return $this->rejectedMessages->find($streamId);
    }

    /**
     * @param string[] $events
     */
    protected function assertReceivedEventsInOrder(array $events): void
    {
        $this->assertEquals(
            $events,
            $this->messagesReceivedByConsumer
        );
    }

    protected function receivedNewEvent(string $json): bool
    {
        return collect($this->messagesReceivedByConsumer)
            ->contains($json);
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Domain\Client\Repository\StreamEventRepository;
use App\Infrastructure\Message\ReadStream as ReadStreamMessage;
use App\Infrastructure\MessageHandler;
use App\Infrastructure\SocketConnectionsPool;
use PearTreeWeb\EventSourcerer\Common\Model\MessageType;
use PearTreeWeb\EventSourcerer\Common\Service\CreateMessage;
use Psr\Log\LoggerInterface;
use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class ReadStream implements MessageHandler
{
    public function __construct(
        private StreamEventRepository $streamEventRepository,
        private LoggerInterface $socketLogger,
    ) {}

    public function canHandle(MessageType $messageType): bool
    {
        return $messageType === self::handles();
    }

    public static function handles(): MessageType
    {
        return MessageType::ReadStream;
    }

    /**
     * @param iterable<ReadStreamMessage> $messages
     */
    public function handle(
        ConnectionInterface $connection,
        SocketConnectionsPool $connectionsPool,
        iterable $messages,
        OutputInterface $output
    ): void {
        foreach ($messages as $message) {
            $events = $this->streamEventRepository->withStreamId(
                $message->streamId,
                $message->start->toInt(),
            );

            foreach ($events as $event) {
                $connection->write(
                    CreateMessage::forNewEvent(
                        json_encode([
                            'version' => $event->eventVersion(),
                            'eventName' => $event->getEventName(),
                            'properties' => $event->getPayloadProperties()->toScalarArray(),
                        ], JSON_THROW_ON_ERROR)
                    ),
                );

                $this->socketLogger->info(
                    sprintf(
                        'Sent event for stream "%s" with sequence "%d" to "%s"',
                        $event->getStreamId(),
                        $event->getSequence(),
                        $connection->getRemoteAddress()
                    )
                );
            }
        }
    }
}

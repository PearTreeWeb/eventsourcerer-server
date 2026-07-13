<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use ApiPlatform\Metadata\Post;
use App\ApiDto\StreamEvent;
use App\Infrastructure\Message\WriteNewEvent as WriteNewEventMessage;
use App\Infrastructure\MessageHandler;
use App\Infrastructure\SocketConnectionsPool;
use App\Processor\StreamEventProcessor;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\MessageType;
use PearTreeWeb\EventSourcerer\Common\Service\CreateMessage;
use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

final readonly class WriteNewEvent implements MessageHandler
{
    public function __construct(private StreamEventProcessor $streamEventProcessor) {}

    public function canHandle(MessageType $messageType): bool
    {
        return self::handles() === $messageType;
    }

    public static function handles(): MessageType
    {
        return MessageType::WriteNewEvent;
    }

    /**
     * @param iterable<WriteNewEventMessage> $messages
     */
    public function handle(
        ConnectionInterface $connection,
        SocketConnectionsPool $connectionsPool,
        iterable $messages,
        OutputInterface $output
    ): void {
        foreach ($messages as $message) {
            $streamEvent = new StreamEvent();
            $streamEvent->stream = $message->streamId->toString();
            $streamEvent->event = $message->eventName->toString();
            $streamEvent->version = $message->eventVersion->toInt();
            $streamEvent->properties = $message->payload;
            $streamEvent->expectedVersion = $message->expectedVersion;

            $expectedVersion = $message->expectedVersion
                ? Checkpoint::fromInt($message->expectedVersion)
                : Checkpoint::zero();

            try {
                $this->streamEventProcessor->process(
                    data: $streamEvent,
                    operation: new Post(),
                    context: [
                        'request' => self::parameterBag($message->metadata),
                    ]
                );

                $connection->write(
                    CreateMessage::forAcceptanceOfNewEvent($message->streamId, $expectedVersion)
                );
            } catch (\Throwable $e) {
                $output->writeln('<error>' . $e->getMessage() . '</error>');
                $connection->write(
                    CreateMessage::forRejectionOfNewEvent($message->streamId, $expectedVersion)
                );
            }
        }
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private static function parameterBag(array $metadata): object
    {
        $request = new \StdClass();
        $request->headers = new ParameterBag($metadata);

        return $request;
    }
}

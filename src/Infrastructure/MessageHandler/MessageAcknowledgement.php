<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Infrastructure\EventSourcerer\Service\AcknowledgeEvent;
use App\Infrastructure\Message\Acknowledgement;
use App\Infrastructure\MessageHandler;
use App\Infrastructure\SocketConnectionsPool;
use PearTreeWeb\EventSourcerer\Common\Model\MessageType;
use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class MessageAcknowledgement implements MessageHandler
{
    public function __construct(private AcknowledgeEvent $acknowledgeEvent) {}

    public function canHandle(MessageType $messageType): bool
    {
        return self::handles() === $messageType;
    }

    /**
     * @param iterable<Acknowledgement> $messages
     */
    public function handle(
        ConnectionInterface $connection,
        SocketConnectionsPool $connectionsPool,
        iterable $messages,
        OutputInterface $output
    ): void {
        foreach ($messages as $acknowledgement) {
            $this->acknowledgeEvent->acknowledge(
                $acknowledgement->checkpoint,
                $acknowledgement->allStreamCheckpoint,
                $acknowledgement->applicationId,
                $acknowledgement->streamId,
                $acknowledgement->workerId,
            );
        }
    }

    public static function handles(): MessageType
    {
        return MessageType::Acknowledgement;
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Infrastructure\Message\Rejection as RejectionMessage;
use App\Infrastructure\MessageHandler;
use App\Infrastructure\Repository\RejectedMessages;
use App\Infrastructure\SocketConnectionsPool;
use PearTreeWeb\EventSourcerer\Common\Model\MessageType;
use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class Rejection implements MessageHandler
{
    public function __construct(private RejectedMessages $rejectedMessages) {}

    public function canHandle(MessageType $messageType): bool
    {
        return $messageType === self::handles();
    }

    public static function handles(): MessageType
    {
        return MessageType::RejectEvent;
    }

    /**
     * @param iterable<RejectionMessage> $messages
     */
    public function handle(
        ConnectionInterface $connection,
        SocketConnectionsPool $connectionsPool,
        iterable $messages,
        OutputInterface $output
    ): void {
        $this->rejectedMessages->add(...$messages);
    }
}

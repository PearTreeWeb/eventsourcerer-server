<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Infrastructure\Message\Message;
use PearTreeWeb\EventSourcerer\Common\Model\MessageType;
use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface MessageHandler
{
    public function canHandle(MessageType $messageType): bool;

    public static function handles(): MessageType;

    /**
     * @param iterable<Message> $messages
     */
    public function handle(
        ConnectionInterface $connection,
        SocketConnectionsPool $connectionsPool,
        iterable $messages,
        OutputInterface $output
    ): void;
}

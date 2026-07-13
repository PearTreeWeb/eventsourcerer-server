<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Command\StartCatchupProcess;
use App\Infrastructure\Message\CatchupRequest as CatchupRequestMessage;
use App\Infrastructure\MessageHandler;
use App\Infrastructure\SocketConnectionsPool;
use PearTreeWeb\EventSourcerer\Common\Model\MessageType;
use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

final readonly class CatchupRequest implements MessageHandler
{
    public function canHandle(MessageType $messageType): bool
    {
        return self::handles() === $messageType;
    }

    /**
     * @param iterable<CatchupRequestMessage> $messages
     */
    public function handle(
        ConnectionInterface $connection,
        SocketConnectionsPool $connectionsPool,
        iterable $messages,
        OutputInterface $output
    ): void {
        foreach ($messages as $message) {
            $process = new Process(
                command: [
                    'bin/console',
                    StartCatchupProcess::COMMAND,
                    $message->applicationId->toString(),
                    $message->streamId->toString(),
                    $message->workerId->toString(),
                ],
                timeout: null,
            );

            $process->setOptions(['create_new_console' => true]);
            $process->start();
            $output->writeln('Running process ' . $process->getPid());
        }
    }

    public static function handles(): MessageType
    {
        return MessageType::CatchupRequest;
    }
}

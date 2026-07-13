<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Domain\Client\Model\ApplicationWorker;
use App\Infrastructure\Message\ProvideIdentity as ProvideIdentityMessage;
use App\Infrastructure\MessageHandler;
use App\Infrastructure\Repository\WorkerRepository;
use App\Infrastructure\SocketConnectionsPool;
use PearTreeWeb\EventSourcerer\Common\Model\MessageType;
use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class ProvideIdentity implements MessageHandler
{
    public function __construct(private WorkerRepository $workerRepository) {}

    public function canHandle(MessageType $messageType): bool
    {
        return self::handles() === $messageType;
    }

    /**
     * @param iterable<ProvideIdentityMessage> $messages
     */
    public function handle(
        ConnectionInterface $connection,
        SocketConnectionsPool $connectionsPool,
        iterable $messages,
        OutputInterface $output
    ): void {
        foreach ($messages as $message) {
            $this->workerRepository->add(
                new ApplicationWorker(
                    $message->applicationId,
                    $message->workerId,
                )
            );

            $connectionsPool->identify(
                $connection,
                $message->applicationId,
                $message->applicationType,
                $message->workerId
            );
        }
    }

    public static function handles(): MessageType
    {
        return MessageType::ProvideIdentity;
    }
}

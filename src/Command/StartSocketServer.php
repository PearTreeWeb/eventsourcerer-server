<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\Connection\Service\RecordConnection;
use App\Infrastructure\MessageHandler;
use App\Infrastructure\MessageHandlers;
use App\Infrastructure\Repository\ActiveCatchupStreams;
use App\Infrastructure\Repository\WorkerRepository;
use App\Infrastructure\SocketConnectionsPool;
use App\Infrastructure\SocketServer;
use Evenement\EventEmitterInterface;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:socket-server:start')]
final readonly class StartSocketServer
{
    public function __construct(
        private LoggerInterface $socketLogger,
        private ?EventEmitterInterface $eventEmitter,
        /** @var iterable<MessageHandler> $messageHandlers */
        private iterable $messageHandlers,
        private ClockInterface $clock,
        private RecordConnection $recordConnection,
        private ActiveCatchupStreams $activeCatchupStreams,
        private WorkerRepository $workerRepository,
    ) {}

    public function __invoke(OutputInterface $output): int
    {
        if (!$this->eventEmitter) {
            return Command::FAILURE;
        }

        SocketServer::start(
            SocketConnectionsPool::create($this->recordConnection),
            $this->socketLogger,
            MessageHandlers::create($this->messageHandlers),
            $this->eventEmitter,
            $this->clock,
            $this->activeCatchupStreams,
            $output,
            $this->workerRepository,
        );

        $output->writeln('<info>Server running</info>');

        return Command::SUCCESS;
    }
}

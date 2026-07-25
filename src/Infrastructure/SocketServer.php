<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Infrastructure\Factory\MessageDecoder;
use App\Infrastructure\Repository\ActiveCatchupStreams;
use App\Infrastructure\Repository\WorkerRepository;
use DateTimeInterface;
use Evenement\EventEmitterInterface;
use PearTreeWeb\EventSourcerer\Common\Factory\MessageTypeFactory;
use PearTreeWeb\EventSourcerer\Common\Model\MessageType;
use PearTreeWeb\EventSourcerer\Common\Model\MessageMarkup;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class SocketServer
{
    public static function start(
        SocketConnectionsPool $connectionsPool,
        LoggerInterface $socketLogger,
        MessageHandlers $handlers,
        EventEmitterInterface $eventEmitter,
        ClockInterface $clock,
        ActiveCatchupStreams $activeCatchupStreams,
        OutputInterface $output,
        WorkerRepository $workerRepository,
    ): void {
        $socketLogger->info('Server started at ' . $clock->now()->format(DateTimeInterface::ATOM));

        $eventEmitter->on('connection', function (ConnectionInterface $conn) use (&$connectionsPool, $handlers, $socketLogger, &$output, $eventEmitter) {
            $connectionsPool->addConnection($conn);

            $connectionMessage = sprintf(
                '%s (%s) is connected at %s',
                $conn->getLocalAddress(),
                $conn->getLocalAddress(),
                (new \DateTimeImmutable())->format('H:i:s')
            );

            $output->writeln($connectionMessage);
            $socketLogger->info($connectionMessage);
            $conn->on('data', self::directEvent($handlers, $conn, $connectionsPool, $socketLogger, $output));
            $conn->on('close', function() use ($conn, $eventEmitter) {
                $eventEmitter->emit('disconnect', [$conn]);
            });
            $conn->on('error', function(\Exception $e) use ($conn, $socketLogger, $eventEmitter) {
                $socketLogger->error(sprintf('Connection error on %s: %s', $conn->getRemoteAddress() ?? 'unknown', $e->getMessage()));
                $eventEmitter->emit('disconnect', [$conn]);
                $conn->close();
            });
        });

        $eventEmitter->on(
            'disconnect',
            function (ConnectionInterface $conn) use (
                &$connectionsPool,
                $socketLogger,
                $activeCatchupStreams,
                &$output,
                $workerRepository,
            ): void {
                $applicationId = $connectionsPool->applicationIdFor($conn);
                $workerId = $connectionsPool->workerIdFor($conn);

                $connectionsPool->removeConnection($conn);

                if (null !== $applicationId) {
                    $activeCatchupStreams->removeAllForApplication($applicationId);
                }

                if (null !== $workerId) {
                    $workerRepository->remove($workerId);
                }

                $disconnectedMessage = $conn->getLocalAddress() . ' disconnected';

                $socketLogger->info($disconnectedMessage);
                $output->writeln($disconnectedMessage);
            }
        );
    }

    private static function directEvent(
        MessageHandlers $handlers,
        ConnectionInterface $connection,
        SocketConnectionsPool $connectionsPool,
        LoggerInterface $logger,
        OutputInterface $output
    ): callable {
        return static function (string $rawMessage) use ($connection, &$connectionsPool, $handlers, $logger, &$output): void {
            $parsedMessages = explode(MessageMarkup::NewEventParser->value, $rawMessage);

            foreach ($parsedMessages as $message) {
                if ('' === $message) {
                    continue;
                }

                $logger->info('Received: ' . $message);

                $messageType = MessageTypeFactory::fromMessage($message);

                if ($handler = $handlers->findFor($messageType)) {
                    $handler->handle(
                        $connection,
                        $connectionsPool,
                        MessageDecoder::decode($messageType, $message),
                        $output
                    );
                }

                if (null === $handler) {
                    $logger->info('Unhandled message: ' . $message);
                }
            }
        };
    }
}

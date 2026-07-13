<?php

declare(strict_types=1);

namespace App\Infrastructure\MessageHandler;

use App\Domain\Application\Repository\ApplicationCheckpointRepository;
use App\Infrastructure\Message\NewEvent as NewEventMessage;
use App\Infrastructure\MessageHandler;
use App\Infrastructure\Repository\RejectedMessages;
use App\Infrastructure\Repository\WorkerRepository;
use App\Infrastructure\SocketConnectionsPool;
use App\Infrastructure\WorkerConnection;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\MessageType;
use PearTreeWeb\EventSourcerer\Common\Service\CreateMessage;
use Psr\Log\LoggerInterface;
use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class NewEvent implements MessageHandler
{
    public function __construct(
        private ApplicationCheckpointRepository $applicationCheckpointRepository,
        private RejectedMessages $rejectedMessages,
        private WorkerRepository $workerRepository,
        private LoggerInterface $socketLogger,
    ) {}

    public function canHandle(MessageType $messageType): bool
    {
        return self::handles() === $messageType;
    }

    /**
     * @param iterable<NewEventMessage> $messages
     */
    public function handle(
        ConnectionInterface $connection,
        SocketConnectionsPool $connectionsPool,
        iterable $messages,
        OutputInterface $output
    ): void {
        foreach ($connectionsPool->allApplicationConnections() as $workerConnection) {
            $applicationId = $workerConnection->applicationId;

            foreach ($messages as $message) {
                $decodedMessage = json_decode($message->json, true, 512, JSON_THROW_ON_ERROR);
                $allSequence = Checkpoint::fromInt($decodedMessage['allSequence']);
                $streamSequence = $message->checkpoint->toInt();
                $streamId = $message->streamId->toString();

                // Determine target worker for this message
                $targetWorkerIdString = $decodedMessage['workerId'] ?? null;

                // For live events (no workerId yet), assign a single worker for this application
                if (self::hasNoAssignedWorkerId($decodedMessage)) {
                    if ($assigned = $this->workerRepository->oneWorkerForApplication($applicationId)) {
                        $message = $message->cloneWithWorkerId($assigned);
                        $targetWorkerIdString = $assigned->toString();
                    }
                }

                // If a target worker is defined (either catch-up or assigned), only deliver to that worker
                if (null !== $targetWorkerIdString && $workerConnection->workerId->toString() !== $targetWorkerIdString) {
                    continue;
                }

                $currentCheckpoint = $this
                    ->applicationCheckpointRepository
                    ->findOrCreate($applicationId, $message->streamId);

                $expectedNextCheckpoint = Checkpoint::fromInt($currentCheckpoint->getCheckpoint())->increment();

                if ($message->checkpoint->isGreaterThan($expectedNextCheckpoint)) {
                    $connection->write(
                        CreateMessage::forRejection(
                            $message->streamId,
                            $applicationId,
                            $message->checkpoint,
                            $message->json
                        )->toString()
                    );
                    // Note: we still proceed to broadcasting; per‑worker/per‑stream guard below
                    // enforces delivery order. The rejection simply informs the sender that the
                    // application checkpoint has not advanced yet.
                }

                $applicationConnection = $workerConnection->connection;

                // Do not compare local addresses; they are identical for all inbound connections.
                // Use object identity to avoid echoing back to the same socket (defensive; they are distinct here).
                if ($applicationConnection !== $connection) {
                    // Per-worker, per-stream monotonicity guard
                    $last = $this->workerRepository->lastForwardedCheckpoint($workerConnection->workerId, $message->streamId);
                    $isCatchupEvent = isset($decodedMessage['catchupRequestStream']);

                    if (null !== $last && $streamSequence <= $last) {
                        if ($isCatchupEvent && $streamSequence < $last) {
                            // Catchup restarted from an earlier position; reset the stored checkpoint
                            $this->workerRepository->setLastForwardedCheckpoint(
                                $workerConnection->workerId,
                                $message->streamId,
                                $streamSequence - 1
                            );
                        } else {
                            // Stale or duplicate for this worker/stream; skip
                            $this->socketLogger->info(
                                sprintf(
                                    'Skipped stale/duplicate event stream=%s seq=%d all=%d for worker %s (last=%d)',
                                    $streamId,
                                    $streamSequence,
                                    $allSequence->toInt(),
                                    $workerConnection->workerId->toString(),
                                    $last
                                )
                            );

                            continue;
                        }
                    }

                    if (null !== $last && $streamSequence > ($last + 1)) {
                        // Gap detected for this worker/stream
                        if ($isCatchupEvent) {
                            // A new catchup has started from a different position; reset the stored checkpoint
                            $this->workerRepository->setLastForwardedCheckpoint(
                                $workerConnection->workerId,
                                $message->streamId,
                                $streamSequence - 1
                            );
                        } else {
                            $this->socketLogger->warning(
                                sprintf(
                                    'Detected gap for worker %s on stream=%s: last=%d, incoming=%d (all=%d). Not forwarding.',
                                    $workerConnection->workerId->toString(),
                                    $streamId,
                                    $last,
                                    $streamSequence,
                                    $allSequence->toInt()
                                )
                            );

                            continue;
                        }
                    }

                    $this->broadcastEventToOtherConnections(
                        $applicationConnection,
                        $message,
                        $workerConnection,
                        $allSequence,
                    );

                    // Update last forwarded checkpoint for this worker/stream only after successful write
                    $this->workerRepository->setLastForwardedCheckpoint(
                        $workerConnection->workerId,
                        $message->streamId,
                        $streamSequence
                    );
                }

                if ($rejectedMessages = $this->rejectedMessages->find($message->streamId)) {
                    foreach ($rejectedMessages as $rejectedMessage) {
                        if ($rejectedMessage->checkpoint()->isSameAs($message->checkpoint->increment())) {
                            // Apply the same guard for re-sent rejected messages
                            $resent = $rejectedMessage->event();
                            $resentDecoded = json_decode($resent->json, true, 512, JSON_THROW_ON_ERROR);
                            $resentAll = Checkpoint::fromInt($resentDecoded['allSequence']);
                            $resentSeq = $resent->checkpoint->toInt();

                            $last = $this->workerRepository->lastForwardedCheckpoint($workerConnection->workerId, $resent->streamId);

                            if (null === $last || $resentSeq === $last + 1) {
                                $this->broadcastEventToOtherConnections(
                                    $applicationConnection,
                                    $resent,
                                    $workerConnection,
                                    $resentAll,
                                );

                                $this->workerRepository->setLastForwardedCheckpoint(
                                    $workerConnection->workerId,
                                    $resent->streamId,
                                    $resentSeq
                                );
                            }

                            $this->rejectedMessages->remove($rejectedMessage);
                        }
                    }
                }
            }
        }
    }

    public static function handles(): MessageType
    {
        return MessageType::NewEvent;
    }

    private function broadcastEventToOtherConnections(
        ConnectionInterface $applicationConnection,
        NewEventMessage $message,
        WorkerConnection $workerConnection,
        Checkpoint $allSequence,
    ): void {
        $applicationConnection->write(
            CreateMessage::forNewEvent($message->json)->toString(),
        );

        $this->socketLogger->info(
            sprintf(
                'Forwarded event stream=%s seq=%d all=%d to worker %s',
                $message->streamId->toString(),
                $message->checkpoint->toInt(),
                $allSequence->toInt(),
                $workerConnection->workerId->toString()
            )
        );
    }

    private static function hasNoAssignedWorkerId(mixed $decodedMessage): bool
    {
        return !isset($decodedMessage['workerId'])
            && !isset($decodedMessage['catchupRequestStream']);
    }
}

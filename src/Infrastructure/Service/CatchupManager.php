<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\Application\Repository\ActiveCatchupRepository;
use App\Domain\Application\Repository\ApplicationRepository;
use App\Domain\Client\Repository\StreamEventRepository;
use App\Domain\Common\Service\EventBroadcaster;
use App\Entity\StreamEvent;
use App\Infrastructure\Exception\CatchupIsStuck;
use App\Infrastructure\Exception\CouldNotBroadcastEvent;
use App\Infrastructure\FormatEvent;
use App\Infrastructure\Repository\ActiveCatchupStreams;
use App\Infrastructure\Repository\WorkerRepository;
use App\Repository\Postgres\PostgresStreamEventRepository;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class CatchupManager
{
    private const int MAX_CATCHUP_STATUS_CHECKS = 100;

    public function __construct(
        #[Autowire(service: PostgresStreamEventRepository::class)]
        private StreamEventRepository $streamEventRepository,
        private ActiveCatchupStreams $activeCatchupStreams,
        private ApplicationRepository $applicationRepository,
        private ActiveCatchupRepository $activeCatchupRepository,
        private EventBroadcaster $eventBroadcaster,
        private LoggerInterface $socketLogger,
        private CatchupStatusProvider $catchupStatusProvider,
        /** @phpstan-ignore property.onlyWritten */
        private WorkerRepository $workerRepository,
    ) {}

    public function startFor(
        ApplicationId $applicationId,
        StreamId $streamId,
        WorkerId $workerId,
    ): void {
        $application = $this->applicationRepository->byIdStrict($applicationId);
        $finalCheckpoint = $this->streamEventRepository->maxSequenceFor($streamId);

        $catchupStartedMessage = sprintf(
            'Catchup request made by application "%s". Final checkpoint is %s. Active catchup streams are: "%s"',
            $application->name(),
            $finalCheckpoint->toString(),
            $this->activeCatchupStreams->summary($applicationId)
        );

        $this->socketLogger->info($catchupStartedMessage);
        $this->activeCatchupRepository->addFor($applicationId);

        $eventsSent = 0;

        $this->processCatchup(
            $applicationId,
            $streamId,
            $eventsSent,
            $workerId,
            Checkpoint::zero(),
            Checkpoint::zero()
        );
    }

    private function processCatchup(
        ApplicationId $applicationId,
        StreamId $streamId,
        int $eventsProcessed,
        WorkerId $workerId,
        Checkpoint $lastProcessedAllStreamCheckpoint,
        Checkpoint $afterCheckpoint,
        ?StreamId $drainStreamId = null,
    ): void {
        $this->waitIfCatchupIsPaused($workerId, $streamId, $afterCheckpoint);

        $streamEvent = $this->streamEventRepository->oldestUnworkedEvent(
            $applicationId,
            $this->activeCatchupStreams->allForApplication($applicationId),
            $lastProcessedAllStreamCheckpoint,
            $drainStreamId,
            $afterCheckpoint
        );

        if (null !== $streamEvent) {
            $this->catchupStatusProvider->setAsRunningFor($workerId);

            $activeStreamId = StreamId::fromString($streamEvent->getStreamId());

            $this->activeCatchupStreams->add($activeStreamId, $applicationId);
            $this->socketLogger->info(self::logMessage($streamEvent));

            try {
                $this->eventBroadcaster->broadcastSync(
                    self::formatEvent($streamId, $workerId, FormatEvent::toArray($streamEvent))
                );
            } catch (CouldNotBroadcastEvent $e) {
                $this->socketLogger->error('Catchup broadcast failed, aborting: ' . $e->getMessage());

                return;
            }

            $eventsProcessed++;

            $this->processCatchup(
                $applicationId,
                $streamId,
                $eventsProcessed,
                $workerId,
                Checkpoint::fromInt($streamEvent->getAllSequence()),
                Checkpoint::fromInt($streamEvent->getSequence()),
                StreamId::fromString($streamEvent->getStreamId())
            );

            return;
        }

        if (null !== $drainStreamId) {
            $this->processCatchup(
                $applicationId,
                $streamId,
                $eventsProcessed,
                $workerId,
                Checkpoint::zero(),
                Checkpoint::zero()
            );

            return;
        }

        $this->socketLogger->info(
            sprintf(
                'Catchup finished, no more events found. %d events processed. Active streams are: "%s"',
                $eventsProcessed,
                $this->activeCatchupStreams->summary($applicationId)
            )
        );

        $this->activeCatchupStreams->removeAllForApplication($applicationId);
        $this->activeCatchupRepository->removeFor($applicationId);
    }

    /**
     * @param array{
     *     allSequence: int,
     *     eventVersion: int,
     *     name: string,
     *     number: int,
     *     payload: array<string, mixed>,
     *     stream: string,
     *     occurred: string,
     * } $event
     *
     * @throws \JsonException
     */
    private static function formatEvent(StreamId $streamId, WorkerId $workerId, array $event): string
    {
        $event['workerId'] = $workerId->toString();
        $event['catchupRequestStream'] = $streamId->toString();

        return json_encode($event, JSON_THROW_ON_ERROR);
    }

    private function waitIfCatchupIsPaused(
        WorkerId $workerId,
        StreamId $streamId,
        Checkpoint $lastCheckpoint,
        ?int $i = 0
    ): void {
        if (self::MAX_CATCHUP_STATUS_CHECKS === $i) {
            throw CatchupIsStuck::waitingForMessageAcknowledgementFor(
                $workerId,
                $streamId,
                $lastCheckpoint
            );
        }

        if ($this->catchupStatusProvider->isPausedFor($workerId)) {
            $i++;

            $this->waitIfCatchupIsPaused($workerId, $streamId, $lastCheckpoint, $i);
        }
    }

    private static function logMessage(StreamEvent $streamEvent): string
    {
        return sprintf(
            'Process with ID %d found event with all sequence %d and stream sequence %d',
            getmypid(),
            $streamEvent->getAllSequence(),
            $streamEvent->getSequence()
        );
    }
}

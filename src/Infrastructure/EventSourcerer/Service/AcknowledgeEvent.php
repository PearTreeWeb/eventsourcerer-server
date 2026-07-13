<?php

declare(strict_types=1);

namespace App\Infrastructure\EventSourcerer\Service;

use App\Domain\Application\Repository\ApplicationCheckpointRepository;
use App\Entity\ApplicationCheckpoint;
use App\Infrastructure\Repository\InFlightMessageStreamIds;
use App\Infrastructure\Service\CatchupStatusProvider;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;
use Psr\Log\LoggerInterface;

final readonly class AcknowledgeEvent
{
    public function __construct(
        private LoggerInterface $socketLogger,
        private ApplicationCheckpointRepository $applicationCheckpointRepository,
        private InFlightMessageStreamIds $inFlightMessageStreamIds,
        private CatchupStatusProvider $catchupStatusProvider,
    ) {}

    public function acknowledge(
        Checkpoint $acknowledgementCheckpoint,
        Checkpoint $acknowledgementAllStreamCheckpoint,
        ApplicationId $applicationId,
        StreamId $streamId,
        WorkerId $workerId
    ): void {
        $this->catchupStatusProvider->setAsRunningFor($workerId);
        $this->inFlightMessageStreamIds->removeFor($applicationId, $streamId);

        $checkpoint = $this->applicationCheckpointRepository->findOrCreate($applicationId, $streamId);
        $allStreamCheckpoint = $this->applicationCheckpointRepository->findOrCreate($applicationId, StreamId::allStream());

        $this->setCheckpointIfIncreased($checkpoint, $acknowledgementCheckpoint);
        $this->setCheckpointIfIncreased($allStreamCheckpoint, $acknowledgementAllStreamCheckpoint);

        $this->socketLogger->info(
            sprintf(
                'Acknowledged event. Set checkpoint to %s for stream %s',
                $acknowledgementCheckpoint,
                $streamId
            )
        );
    }

    private function setCheckpointIfIncreased(
        ApplicationCheckpoint $checkpoint,
        Checkpoint $acknowledgementCheckpoint
    ): void {
        if (
            $checkpoint->isNotZero()
            && $checkpoint->isGreaterThanCheckpoint($acknowledgementCheckpoint)
        ) {
            return;
        }

        $this->applicationCheckpointRepository->update(
            $checkpoint->setCheckpoint($acknowledgementCheckpoint->toInt())
        );
    }
}

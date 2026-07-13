<?php

declare(strict_types=1);

namespace App\Infrastructure\EventSourcerer\Service;

use App\Domain\Application\Repository\ApplicationCheckpointRepository;
use App\Domain\Client\Repository\StreamEventRepository;
use App\Entity\StreamEvent;
use App\Infrastructure\Repository\InFlightMessageStreamIds;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

final readonly class EventFetcher
{
    public function __construct(
        private StreamEventRepository $streamEventRepository,
        private InFlightMessageStreamIds $inFlightMessageStreamIds,
        private ApplicationCheckpointRepository $applicationCheckpointRepository
    ) {}

    public function fetchFor(
        ApplicationId $applicationId,
        StreamId $streamId
    ): ?StreamEvent {
        $applicationCheckpoint = Checkpoint::fromInt(
            $this
                ->applicationCheckpointRepository
                ->findOrCreate($applicationId, $streamId)
                ->getCheckpoint()
        );

        $streamEvent = $this
            ->streamEventRepository
            ->fetchOneExcludingStreamIds(
                $applicationCheckpoint->increment(),
                $this->inFlightMessageStreamIds->for($applicationId)
            );

        if (null === $streamEvent) {
            return null;
        }

        $this->inFlightMessageStreamIds->addFor(
            $applicationId,
            StreamId::fromString($streamEvent->getStreamId())
        );

        return $streamEvent;
    }
}

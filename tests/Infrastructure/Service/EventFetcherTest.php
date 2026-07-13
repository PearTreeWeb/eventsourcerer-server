<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Infrastructure\EventSourcerer\Service\EventFetcher;
use App\Infrastructure\Repository\InFlightMessageStreamIds;
use App\Tests\Double\Id;
use App\Tests\Double\Repository\ApplicationCheckpointRepository;
use App\Tests\Double\Repository\StreamEventRepository;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class EventFetcherTest extends TestCase
{
    private EventFetcher                    $eventFetcher;
    private InFlightMessageStreamIds        $inFlightMessages;
    private ApplicationCheckpointRepository $applicationCheckpointRepository;

    protected function setUp(): void
    {
        $this->inFlightMessages                = new InFlightMessageStreamIds(new ArrayAdapter());
        $this->applicationCheckpointRepository = new ApplicationCheckpointRepository([]);

        $this->eventFetcher = new EventFetcher(
            StreamEventRepository::createRepository(),
            $this->inFlightMessages,
            $this->applicationCheckpointRepository
        );
    }

    #[Test]
    public function itNeverAllowsTwoMessagesFromSameStreamToBeReadAtTheSameTime(): void
    {
        $allStream = Id::streamIdAll();
        $applicationId = Id::applicationId();
        $firstEvent = $this->eventFetcher->fetchFor($applicationId, $allStream);
        $secondEvent = $this->eventFetcher->fetchFor($applicationId, $allStream);

        $this->assertEquals(Id::streamId(), StreamId::fromString($firstEvent->getStreamId()));
        $this->assertEquals(Id::streamId2(), StreamId::fromString($secondEvent->getStreamId()));
    }

    #[Test]
    public function itAllowsFromTheSameStreamAfterAck(): void
    {
        $applicationId = Id::applicationId();
        $firstEvent = $this->eventFetcher->fetchFor(Id::applicationId(), Id::streamIdAll());
        $streamId = StreamId::fromString($firstEvent->getStreamId());

        $this->inFlightMessages->removeFor($applicationId, $streamId);

        $checkpoint = $this
            ->applicationCheckpointRepository
            ->findOrCreate($applicationId, StreamId::fromString('*'))
            ->setCheckpoint(1);

        $this->applicationCheckpointRepository->update($checkpoint);

        $secondEvent = $this->eventFetcher->fetchFor(Id::applicationId(), Id::streamIdAll());

        $this->assertEquals(Id::streamId(), StreamId::fromString($firstEvent->getStreamId()));
        $this->assertEquals(Id::streamId(), StreamId::fromString($secondEvent->getStreamId()));
        $this->assertEquals(1, $firstEvent->getAllSequence());
        $this->assertEquals(2, $secondEvent->getAllSequence());
    }
}

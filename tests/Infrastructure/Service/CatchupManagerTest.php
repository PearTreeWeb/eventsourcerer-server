<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Domain\Application\Repository\ActiveCatchupRepository;
use App\Domain\Client\Repository\StreamEventRepository;
use App\Domain\Common\Service\EventBroadcaster;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventVersion;
use App\Domain\Stream\Model\StreamEventId;
use App\Entity\Stream;
use App\Entity\StreamEvent;
use App\Infrastructure\Repository\ActiveCatchupStreams;
use App\Infrastructure\Repository\WorkerRepository;
use App\Infrastructure\Service\CatchupManager;
use App\Infrastructure\Service\CatchupStatusProvider;
use App\Tests\Double\Id;
use App\Tests\Double\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CatchupManagerTest extends TestCase
{
    private const string APPLICATION_ID = 'c0bb9488-bf6a-5756-852c-3ea2677666ec';
    private const string STREAM_ID_1 = 'fe4fe11b-a07a-4c1f-9530-08d497d6f9d3';
    private const string STREAM_ID_2 = '249b7ab0-cf26-443f-923d-8caa9db670ac';
    private const string STREAM_ID_3 = '31e6860b-75fc-49b0-98da-ba5ca896eaf4';
    private const string EVENT_ID = '93b2f8f4-ea14-4d0c-a8c5-21c3fa3043e9';
    private const string EVENT_NAME = 'Test Event';

    #[Test]
    public function itSuccessfullyRunsCatchup(): void
    {
        $activeCatchupStreams = new ActiveCatchupStreams(new ArrayAdapter());
        $eventBroadcaster = $this->createMock(EventBroadcaster::class);

        $catchupManager = new CatchupManager(
            $this->mockStreamEventRepository(),
            $activeCatchupStreams,
            new ApplicationRepository(),
            $this->createStub(ActiveCatchupRepository::class),
            $eventBroadcaster,
            new NullLogger(),
            new CatchupStatusProvider(new ArrayAdapter()),
            new WorkerRepository(new ArrayAdapter()),
        );

        $eventBroadcaster->expects($this->exactly(10))->method('broadcastSync');

        $catchupManager->startFor(
            ApplicationId::fromString(self::APPLICATION_ID),
            StreamId::allStream(),
            Id::workerId(),
        );
    }

    private function mockStreamEventRepository(): StreamEventRepository
    {
        $streamId1 = StreamId::fromString(self::STREAM_ID_1);
        $streamId2 = StreamId::fromString(self::STREAM_ID_2);
        $streamId3 = StreamId::fromString(self::STREAM_ID_3);

        $now = new \DateTimeImmutable();
        $mock = $this->createMock(StreamEventRepository::class);

        $mock->method('maxSequenceFor')->willReturn(Checkpoint::fromInt(10));

        $mock
            ->method('oldestUnworkedEvent')
            ->willReturnOnConsecutiveCalls(
                self::streamEvent('2e0bea2c-d02b-493a-a144-2fdade02dd08', $streamId1, $now)->setAllSequence(1),
                self::streamEvent('1bb5bd72-1cbf-4238-8e00-a347d9ef7252', $streamId1, $now)->setAllSequence(2),
                self::streamEvent('a4f477a1-e4af-4813-9e03-8ea897e64ee0', $streamId1, $now)->setAllSequence(3),
                self::streamEvent('7484a462-8681-4e72-b934-6c41bcea80ed', $streamId2, $now)->setAllSequence(4),
                self::streamEvent('aae36c2f-6ad9-4774-9fa8-9b0dededb3d4', $streamId2, $now)->setAllSequence(5),
                self::streamEvent('c71589f7-69f5-4eaf-b131-2e004369069b', $streamId2, $now)->setAllSequence(6),
                self::streamEvent('9f9b4123-1ecf-4d6b-92fc-715cfefd9ba7', $streamId3, $now)->setAllSequence(7),
                self::streamEvent('04a26357-b326-41c5-a849-c42a70191f7d', $streamId3, $now)->setAllSequence(8),
                self::streamEvent('d902d961-fb20-4566-971b-c9aed645e004', $streamId3, $now)->setAllSequence(9),
                self::streamEvent('1ee2b370-edab-4a2d-bc93-083315ba8c04', $streamId3, $now)->setAllSequence(10),
                null,
                null,
            );

        return $mock;
    }

    private static function stream(StreamId $streamId): Stream
    {
        return Stream::create($streamId, new \DateTimeImmutable());
    }

    private static function streamEvent(string $streamEventId, StreamId $streamId, \DateTimeImmutable $now): StreamEvent
    {
        $eventID = EventId::fromString(self::EVENT_ID);
        $eventName = EventName::fromString(self::EVENT_NAME);
        $eventVersion = EventVersion::fromInt(EventVersion::DEFAULT);

        return StreamEvent::create(
            StreamEventId::fromString($streamEventId),
            $eventID,
            $streamId,
            $eventName,
            $eventVersion,
            new ArrayCollection(),
            self::stream($streamId),
            $now,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Double\Repository;

use App\Domain\Client\Repository\StreamEventRepository as StreamEventRepositoryInterface;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventVersion;
use App\Domain\Stream\Model\StreamEventId;
use App\Domain\Stream\Model\StreamEventPayloadProperties;
use App\Entity\Stream;
use App\Entity\StreamEvent;
use App\Infrastructure\EventSourcerer\Service\GenerateUuid;
use App\Tests\Double\Id;
use Doctrine\Common\Collections\ArrayCollection;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Symfony\Component\Uid\Factory\UuidFactory;

final class StreamEventRepository implements StreamEventRepositoryInterface
{
    private function __construct(
        private array $streamEvents,
    ) {}

    public static function createRepository(): self
    {
        $generateUuid   = new GenerateUuid(new UuidFactory());
        $streamEventId1 = StreamEventId::fromString($generateUuid->random()->toString());
        $streamEventId2 = StreamEventId::fromString($generateUuid->random()->toString());
        $streamEventId3 = StreamEventId::fromString($generateUuid->random()->toString());
        $streamEventId4 = StreamEventId::fromString($generateUuid->random()->toString());
        $streamEventId5 = StreamEventId::fromString($generateUuid->random()->toString());
        $streamEventId6 = StreamEventId::fromString($generateUuid->random()->toString());

        $streamId1 = Id::streamId();
        $streamId2 = Id::streamId2();

        return new self([
            $streamEventId1->toString() => self::sampleStreamEvent($streamEventId1, $streamId1)->setAllSequence(1),
            $streamEventId2->toString() => self::sampleStreamEvent($streamEventId2, $streamId1)->setAllSequence(2),
            $streamEventId3->toString() => self::sampleStreamEvent($streamEventId3, $streamId1)->setAllSequence(3),
            $streamEventId4->toString() => self::sampleStreamEvent($streamEventId4, $streamId1)->setAllSequence(4),
            $streamEventId5->toString() => self::sampleStreamEvent($streamEventId5, $streamId1)->setAllSequence(5),
            $streamEventId6->toString() => self::sampleStreamEvent($streamEventId6, $streamId2)->setAllSequence(1),
        ]);
    }

    private static function sampleStreamEvent(
        StreamEventId $streamEventId,
        StreamId $streamId,
    ): StreamEvent {
        $now = new \DateTimeImmutable();

        return StreamEvent::create(
            $streamEventId,
            EventId::fromString('0e90e2da-1741-410b-804c-bd762280231c'),
            $streamId,
            EventName::fromString('Test Event 1'),
            EventVersion::fromInt(1),
            new ArrayCollection(),
            Stream::create($streamId, $now),
            $now
        );
    }

    public function all(): array
    {
        return $this->streamEvents;
    }

    public function create(StreamEvent $streamEvent): StreamEvent
    {
        $this->streamEvents[] = $streamEvent;

        return $streamEvent;
    }

    public function update(StreamEvent $streamEvent): StreamEvent
    {
        return $streamEvent;
    }

    public function find(StreamEventId $id): ?StreamEvent
    {
        return null;
    }

    public function paginated(StreamId $streamId, int $start, int $limit, bool $descending = true): array
    {
        return $this->streamEvents;
    }

    public function withStreamIdPaginated(
        StreamId $id,
        int $start,
        int $limit,
        ?int $afterSequence = null,
        ?bool $ascending = false,
    ): \Countable&\IteratorAggregate {
        throw new \RuntimeException('not implemented');
    }

    public function paginatedIterable(StreamId $streamId, int $start, int $max): iterable
    {
        return [];
    }

    public function nextSequence(StreamId $streamId): int
    {
        return 0;
    }

    public function nextAllSequence(): int
    {
        return 0;
    }

    public function fetchOneExcludingStreamIds(Checkpoint $checkpoint, array $streamIds): ?StreamEvent
    {
        return collect($this->streamEvents)
            ->filter(static function (StreamEvent $streamEvent) use ($checkpoint, $streamIds) {
                return !\in_array($streamEvent->getStreamId(), $streamIds, true)
                    && Checkpoint::fromInt($streamEvent->getAllSequence())->isSameAs($checkpoint);
            })
            ->first();
    }

    public function tombstoneEvents(): void
    {
    }

    public function allIterable(): iterable
    {
        return [];
    }

    public function allIterableRaw(?\DateTimeImmutable $startDate = null, ?\DateTimeImmutable $endDate = null): iterable
    {
        return [];
    }

    public function allAfter(Checkpoint $checkpoint): iterable
    {
        return [];
    }

    public function withStreamId(StreamId $id, int $start, ?int $end = null): iterable
    {
        return [];
    }

    public function maxSequenceFor(StreamId $streamId): Checkpoint
    {
        return Checkpoint::zero();
    }

    public function oldestUnworkedEvent(
        ApplicationId $applicationId,
        iterable $excludeStreamIds,
        Checkpoint $lastProcessedAllStreamCheckpoint,
        ?StreamId $drainStreamId = null,
        ?Checkpoint $afterCheckpoint = null
    ): ?StreamEvent {
        return null;
    }

    public function eventsWithPersonalDataNotEncryptedBefore(\DateTimeImmutable $before, ?int $limit = null): iterable
    {
        return [];
    }

    public function countEventsWithPersonalDataNotEncryptedBefore(\DateTimeImmutable $before): int
    {
        return 0;
    }

    public function payloadPropertiesFor(StreamEventId $streamEventId): StreamEventPayloadProperties
    {
        return StreamEventPayloadProperties::create();
    }
}

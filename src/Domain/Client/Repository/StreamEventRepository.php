<?php

declare(strict_types=1);

namespace App\Domain\Client\Repository;

use App\Domain\Stream\Model\StreamEventId;
use App\Domain\Stream\Model\StreamEventPayloadProperties;
use App\Entity\StreamEvent;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

interface StreamEventRepository
{
    /**
     * @return StreamEvent[]
     */
    public function all(): array;

    /**
     * @return iterable<StreamEvent>
     */
    public function allIterable(): iterable;

    /**
     * Returns one row per stream event for memory-efficient bulk iteration.
     * Each row contains scalar fields plus a `properties` array of property rows.
     *
     * @return iterable<array{id: string, event_id: string, stream_id: string, all_sequence: int, personal_data_has_been_encrypted: bool, event_name: string, event_version: string, recorded_at: string, properties: list<array{event_property_id: string, prop_name: string, prop_value: string, event_name: string, event_version: string, recorded_at: string, stream_id: string}>}>
     */
    public function allIterableRaw(?\DateTimeImmutable $startDate = null, ?\DateTimeImmutable $endDate = null): iterable;

    /**
     * @return iterable<StreamEvent>
     */
    public function allAfter(Checkpoint $checkpoint): iterable;

    public function create(StreamEvent $streamEvent): StreamEvent;

    public function update(StreamEvent $streamEvent): StreamEvent;

    public function find(StreamEventId $id): ?StreamEvent;

    /**
     * @return StreamEvent[]
     */
    public function paginated(StreamId $streamId, int $start, int $limit, bool $descending = true): array;

    /**
     * @return Paginator<StreamEvent>
     */
    public function withStreamIdPaginated(
        StreamId $id,
        int $start,
        int $limit,
        ?int $afterSequence = null,
        ?bool $ascending = false,
    ): \Countable&\IteratorAggregate;

    /**
     * @return iterable<StreamEvent>
     */
    public function withStreamId(StreamId $id, int $start, ?int $end = null): iterable;

    /**
     * @return iterable<StreamEvent>
     */
    public function paginatedIterable(StreamId $streamId, int $start, int $max): iterable;

    public function nextSequence(StreamId $streamId): int;

    public function nextAllSequence(): int;

    /**
     * @param string[] $streamIds
     */
    public function fetchOneExcludingStreamIds(Checkpoint $checkpoint, array $streamIds): ?StreamEvent;

    public function tombstoneEvents(): void;

    public function maxSequenceFor(StreamId $streamId): Checkpoint;

    /**
     * @param iterable<StreamId> $excludeStreamIds
     */
    public function oldestUnworkedEvent(
        ApplicationId $applicationId,
        iterable $excludeStreamIds,
        Checkpoint $lastProcessedAllStreamCheckpoint,
        ?StreamId $drainStreamId = null,
        ?Checkpoint $afterCheckpoint = null,
    ): ?StreamEvent;

    /**
     * @return iterable<StreamEvent>
     */
    public function eventsWithPersonalDataNotEncryptedBefore(\DateTimeImmutable $before, ?int $limit = null): iterable;

    public function countEventsWithPersonalDataNotEncryptedBefore(\DateTimeImmutable $before): int;

    public function payloadPropertiesFor(StreamEventId $streamEventId): StreamEventPayloadProperties;
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Client\Repository\StreamEventRepository;
use App\Domain\Event\Model\EventPayloadProperty;
use App\Domain\Event\Model\EventPropertyId;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Model\EventPropertyValue;
use App\Domain\Stream\Model\StreamEventId;
use App\Domain\Stream\Model\StreamEventPayloadProperties;
use App\Domain\Stream\Model\StreamEventPayloadProperty;
use App\Entity\ApplicationCheckpoint;
use App\Entity\Event;
use App\Entity\EventProperty;
use App\Entity\StreamEvent;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

final class DoctrineStreamEventRepository implements StreamEventRepository
{
    /**
     * @var EntityRepository<StreamEvent>
     */
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Connection $connection
    ) {
        $this->repository = $entityManager->getRepository(StreamEvent::class);
    }

    public function create(StreamEvent $streamEvent): StreamEvent
    {
        $this->entityManager->persist($streamEvent);

        foreach ($streamEvent->getProperties() as $property) {
            $this->entityManager->persist($property);
        }

        $this->entityManager->flush();

        return $streamEvent;
    }

    public function update(StreamEvent $streamEvent): StreamEvent
    {
        $this->entityManager->flush();

        return $streamEvent;
    }

    public function find(StreamEventId $id): ?StreamEvent
    {
        return $this->repository->find($id->toString());
    }

    public function paginated(StreamId $streamId, int $start, int $limit, bool $descending = true): array
    {
        $order = $descending
            ? 'DESC'
            : 'ASC';

        if ($streamId->isAllStream()) {
            return $this
                ->repository
                ->findBy(
                    criteria: [],
                    orderBy: ['allSequence' => $order],
                    limit: $limit,
                    offset: $start
                );
        }

        return $this->repository->findBy(
            criteria: [
                'streamId' => $streamId,
                'tombstoned' => false,
            ],
            orderBy: ['sequence' => $order],
            limit: $limit,
            offset: $start
        );
    }

    public function withStreamIdPaginated(
        StreamId $id,
        int $start,
        int $limit,
        ?int $afterSequence = null,
        ?bool $ascending = false,
    ): \Countable&\IteratorAggregate {
        $orderBy = $ascending
            ? 'ASC'
            : 'DESC';

        $queryBuilder = $this
            ->repository
            ->createQueryBuilder('s')
            ->select('s')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->where('s.tombstoned = false')
            ->orderBy('s.allSequence', $orderBy);

        if (null !== $afterSequence) {
            $sequenceField = $id->isAllStream() ? 's.allSequence' : 's.sequence';
            $queryBuilder
                ->andWhere($sequenceField . ' > :afterSequence')
                ->setParameter('afterSequence', $afterSequence);
        }
        if (!$id->isAllStream()) {
            $queryBuilder
                ->setParameter('streamId', $id->toString())
                ->andWhere('s.streamId = :streamId')
                ->orderBy('s.sequence', 'DESC');
        }

        return new Paginator($queryBuilder->getQuery(), fetchJoinCollection: false);
    }

    public function all(): array
    {
        return $this->repository->findBy([
            'tombstoned' => false,
        ]);
    }

    public function allIterable(): iterable
    {
        return $this
            ->repository
            ->createQueryBuilder('s')
            ->where('s.tombstoned = false')
            ->getQuery()
            ->toIterable();
    }

    public function allIterableRaw(?\DateTimeImmutable $startDate = null, ?\DateTimeImmutable $endDate = null): iterable
    {
        $sql = <<<SQL
            SELECT se.id, se.event_id, se.stream_id, se.all_sequence, se.personal_data_has_been_encrypted,
                   sep.name AS prop_name, sep.serialized_value AS prop_value
            FROM stream_event se
            LEFT JOIN stream_event_property sep ON sep.stream_event_id = se.id
            WHERE se.tombstoned = false
            ORDER BY se.all_sequence ASC
        SQL;

        yield from $this->connection->executeQuery($sql)->iterateAssociative();
    }

    public function allAfter(Checkpoint $checkpoint): iterable
    {
        return $this
            ->repository
            ->createQueryBuilder('s')
            ->where('s.tombstoned = false')
            ->andWhere('s.allSequence > :allSequence')
            ->setParameter('allSequence', $checkpoint->toInt())
            ->getQuery()
            ->toIterable();
    }

    public function paginatedIterable(StreamId $streamId, int $start, int $max): iterable
    {
        $qb = $this->repository->createQueryBuilder('se');

        $qb->select('se');

        if (!$streamId->isAllStream()) {
            $qb->where('s.streamId = :streamId');
        }

        $qb->setFirstResult($start)
           ->setMaxResults($max)
           ->where('se.tombstoned = false');

        $q = $qb->getQuery();

        if (!$streamId->isAllStream()) {
            $q->setParameter('streamId', $streamId->toString());
        }

        $qb->orderBy('se.allSequence', 'DESC');

        return $q->toIterable();
    }

    public function nextSequence(StreamId $streamId): int
    {
        $sql = <<<SQL
            SELECT COALESCE(res.stream_sequence, 0) +1 AS stream_sequence
            FROM (
                SELECT (
                    SELECT MAX(sequence)
                    FROM stream_event
                    WHERE stream_id = :streamId 
                    LIMIT 1
                ) AS stream_sequence
            ) res
        SQL;

        $query = $this->connection->prepare($sql);

        $query->bindValue('streamId', $streamId->toString());

        return $query->executeQuery()->fetchOne();
    }

    public function nextAllSequence(): int
    {
        $sql = <<<SQL
            SELECT MAX(all_sequence) +1
            FROM stream_event
            LIMIT 1
        SQL;

        return $this->connection->executeQuery($sql)->fetchOne() ?? 1;
    }

    public function fetchOneExcludingStreamIds(Checkpoint $checkpoint, array $streamIds): ?StreamEvent
    {
        $qb = $this->repository->createQueryBuilder('se');

        if (!empty($streamIds)) {
            $qb->where(
                $qb->expr()->notIn(
                    'se.streamId',
                    ':streamIds'
                )
            )
                ->andWhere('se.tombstoned = false')
                ->setParameter('streamIds', $streamIds);
        }

        try {
            return $qb
                ->where('se.allSequence = :checkpoint')
                ->andWhere('se.tombstoned = false')
                ->setParameter('checkpoint', $checkpoint->increment()->toInt())
                ->setMaxResults(1)
                ->orderBy('se.allSequence', 'ASC')
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException) {
            return null;
        }
    }

    public function tombstoneEvents(): void
    {
        $sql = <<<SQL
            UPDATE stream_event
            SET tombstoned = true
            WHERE id IN (
                SELECT se.id
                FROM event e
                JOIN stream_event se ON se.event_id = e.id
                WHERE e.tombstone_after_seconds != 0
                AND NOT se.tombstoned
                AND se.created_at + (interval '1 second' * e.tombstone_after_seconds) > now()::date
            )
        SQL;

        $this->connection->executeQuery($sql);
    }

    public function maxSequenceFor(StreamId $streamId): Checkpoint
    {
        if ($streamId->isAllStream()) {
            return $this->maxAllStreamSequence();
        }

        $sql = <<<SQL
            SELECT MAX(all_sequence)
            FROM stream_event
            WHERE stream_id = :streamId
        SQL;

        $stmt = $this->connection->prepare($sql);

        $stmt->bindValue(':streamId', $streamId->toString());

        $checkpoint = $stmt->executeQuery()->fetchOne() ?? 1;

        return Checkpoint::fromInt($checkpoint);
    }

    public function maxAllStreamSequence(): Checkpoint
    {
        $sql = <<<SQL
            SELECT MAX(all_sequence) 
            FROM stream_event
        SQL;

        $stmt = $this->connection->prepare($sql);
        $checkpoint = $stmt->executeQuery()->fetchOne() ?? 1;

        return Checkpoint::fromInt($checkpoint);
    }

    public function oldestUnworkedEvent(
        ApplicationId $applicationId,
        iterable $excludeStreamIds,
        Checkpoint $lastProcessedAllStreamCheckpoint,
        ?StreamId $drainStreamId = null,
        ?Checkpoint $afterCheckpoint = null,
    ): ?StreamEvent {
        $qb = $this->repository->createQueryBuilder('se');

        $excludeStreamIdsArray = [];

        foreach ($excludeStreamIds as $excludeStreamId) {
            $excludeStreamIdsArray[] = $excludeStreamId->toString();
        }

        $qb
            ->select('se')
            ->join(Event::class, 'e', Join::WITH, 'e.id = se.eventId')
            ->leftJoin(ApplicationCheckpoint::class, 'ac', Join::WITH, '(ac.streamId = se.streamId AND ac.applicationId = :applicationId)')
            ->andWhere('ac.checkpoint < se.sequence')
            ->orWhere('ac.checkpoint IS NULL')
            ->andWhere('e.systemEvent = false')
            ->andWhere($qb->expr()->neq('se.allSequence', ':lastProcessedAllStreamCheckpoint'))
            ->orderBy('se.allSequence', 'ASC')
            ->setParameter('applicationId', $applicationId->toString())
            ->setParameter('lastProcessedAllStreamCheckpoint', $lastProcessedAllStreamCheckpoint->toInt())
            ->setMaxResults(1);

        if ($drainStreamId && $afterCheckpoint) {
            $qb
                ->andWhere('se.streamId = :drainStreamId')
                ->andWhere('se.sequence > :afterCheckpoint')
                ->setParameter('drainStreamId', $drainStreamId->toString())
                ->setParameter('afterCheckpoint', $afterCheckpoint->toInt());
        } else if (!empty($excludeStreamIdsArray)) {
            $qb
                ->andWhere($qb->expr()->notIn('se.streamId', ':excludeStreamIds'))
                ->setParameter('excludeStreamIds', $excludeStreamIdsArray);
        }

        return $qb->getQuery()->getResult()[0] ?? null;
    }

    public function withStreamId(StreamId $id, int $start, ?int $end = null): iterable
    {
        $queryBuilder = $this
            ->repository
            ->createQueryBuilder('s')
            ->select('s')
            ->setFirstResult($start)
            ->where('s.tombstoned = false')
            ->orderBy('s.allSequence', 'ASC');

        if (!$id->isAllStream()) {
            $queryBuilder
                ->setParameter('streamId', $id->toString())
                ->andWhere('s.streamId = :streamId')
                ->orderBy('s.sequence', 'ASC');
        }

        return $queryBuilder->getQuery()->toIterable();
    }

    public function eventsWithPersonalDataNotEncryptedBefore(\DateTimeImmutable $before, ?int $limit = null): iterable
    {
        $qb = $this
            ->repository
            ->createQueryBuilder('se')
            ->select('se')
            ->distinct()
            ->join(EventProperty::class, 'ep', Join::WITH, 'se.eventId = ep.event')
            ->where('se.personalDataHasBeenEncrypted = false')
            ->andWhere('ep.containsPersonalData = true')
            ->andWhere('se.createdAt <= :before')
            ->setParameter('before', $before);

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->toIterable();
    }

    public function countEventsWithPersonalDataNotEncryptedBefore(\DateTimeImmutable $before): int
    {
        return (int) $this
            ->repository
            ->createQueryBuilder('se')
            ->select('COUNT(DISTINCT se.id)')
            ->join(EventProperty::class, 'ep', Join::WITH, 'se.eventId = ep.event')
            ->where('se.personalDataHasBeenEncrypted = false')
            ->andWhere('ep.containsPersonalData = true')
            ->andWhere('se.createdAt <= :before')
            ->setParameter('before', $before)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function payloadPropertiesFor(StreamEventId $streamEventId): StreamEventPayloadProperties
    {
        $sql = <<<SQL
            SELECT sep.event_property_id, sep.name, serialized_value, e.name, e.version,
                   se.created_at AS recorded_at, se.stream_id AS stream_id
            FROM stream_event_property sep
            JOIN stream_event se ON sep.stream_event_id = se.id
            JOIN event e ON e.id = se.event_id
            WHERE stream_event_id = :streamEventId        
        SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'streamEventId' => $streamEventId->toString(),
            ]
        )->fetchAllAssociative();

        $streamEventPayloadProperties =  StreamEventPayloadProperties::fromArray(
            \array_map(
                fn (array $row) => new StreamEventPayloadProperty(
                    EventPropertyId::fromString($row['event_property_id']),
                    new EventPayloadProperty(
                        EventPropertyName::fromString($row['name']),
                        EventPropertyValue::fromString($row['serialized_value']),
                    ),
                ),
                $rows
            ),
        );

        if (!empty($rows)) {
            $streamEventPayloadProperties->add(
                new StreamEventPayloadProperty(
                    EventPropertyId::metadata(),
                    new EventPayloadProperty(
                        EventPropertyName::metadata(),
                        EventPropertyValue::fromString(
                            json_encode([
                                'event' => $rows[0]['name'],
                                'recordedAt' => $rows[0]['recorded_at'],
                                'stream' => $rows[0]['stream_id'],
                                'version' => $rows[0]['version'],
                            ], JSON_THROW_ON_ERROR)
                        )
                    )
                ),
            );
        }

        return $streamEventPayloadProperties;
    }
}

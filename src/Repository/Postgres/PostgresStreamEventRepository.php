<?php

declare(strict_types=1);

namespace App\Repository\Postgres;

use App\Domain\Client\Repository\StreamEventRepository as StreamEventRepositoryInterface;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventPayloadProperty;
use App\Domain\Event\Model\EventPropertyId;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Model\EventPropertyValue;
use App\Domain\Event\Model\EventVersion;
use App\Domain\Stream\Model\StreamEventId;
use App\Domain\Stream\Model\StreamEventPayloadProperties;
use App\Domain\Stream\Model\StreamEventPayloadProperty;
use App\Entity\Stream;
use App\Entity\StreamEvent;
use App\Entity\StreamEventProperty;
use Symfony\Component\Uid\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

final readonly class PostgresStreamEventRepository implements StreamEventRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function create(StreamEvent $streamEvent): StreamEvent
    {
        $this->connection->transactional(function () use ($streamEvent): void {
            $this->connection->executeStatement(
                <<<'SQL'
                    INSERT INTO stream_event (
                        id,
                        event_id,
                        stream_id,
                        event_name,
                        event_version,
                        sequence,
                        all_sequence,
                        created_at,
                        updated_at,
                        tombstoned,
                        personal_data_has_been_encrypted
                    ) VALUES (
                        :id,
                        :eventId,
                        :streamId,
                        :eventName,
                        :eventVersion,
                        :sequence,
                        :allSequence,
                        :createdAt,
                        :updatedAt,
                        :tombstoned,
                        :personalDataHasBeenEncrypted
                    )
                SQL,
                [
                    'id' => $streamEvent->getId()->toRfc4122(),
                    'eventId' => $streamEvent->getEventId()->toUuid()->toRfc4122(),
                    'streamId' => $streamEvent->getStreamId(),
                    'eventName' => $streamEvent->getEventName(),
                    'eventVersion' => $streamEvent->getEventVersion(),
                    'sequence' => $streamEvent->getSequence(),
                    'allSequence' => $streamEvent->getAllSequence(),
                    'createdAt' => $streamEvent->createdAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $streamEvent->updatedAt()->format('Y-m-d H:i:s'),
                    'tombstoned' => $streamEvent->isTombstoned(),
                    'personalDataHasBeenEncrypted' => $streamEvent->hasBeenEncrypted(),
                ],
                [
                    'tombstoned' => ParameterType::BOOLEAN,
                    'personalDataHasBeenEncrypted' => ParameterType::BOOLEAN,
                ]
            );

            foreach ($streamEvent->getProperties() as $property) {
                $this->connection->executeStatement(
                    <<<'SQL'
                        INSERT INTO stream_event_property (
                            stream_event_id,
                            name,
                            type,
                            serialized_value,
                            created_at,
                            updated_at,
                            event_property_id
                        ) VALUES (
                            :streamEventId,
                            :name,
                            :type,
                            :serializedValue,
                            :createdAt,
                            :updatedAt,
                            :eventPropertyId
                        )
                    SQL,
                    [
                        'streamEventId' => $streamEvent->getId()->toRfc4122(),
                        'name' => $property->getName(),
                        'type' => $property->getType(),
                        'serializedValue' => $property->getSerializedValue(),
                        'createdAt' => $streamEvent->createdAt()->format('Y-m-d H:i:s'),
                        'updatedAt' => $streamEvent->updatedAt()->format('Y-m-d H:i:s'),
                        'eventPropertyId' => $property->eventPropertyId()->toRfc4122(),
                    ]
                );
            }
        });

        return $streamEvent;
    }

    public function update(StreamEvent $streamEvent): StreamEvent
    {
        $this->connection->executeStatement(
            <<<SQL
                UPDATE stream_event
                SET
                    sequence = :sequence,
                    all_sequence = :allSequence,
                    updated_at = :updatedAt,
                    tombstoned = :tombstoned,
                    personal_data_has_been_encrypted = :personalDataHasBeenEncrypted
                WHERE id = :id
            SQL,
            [
                'id' => $streamEvent->getId()->toRfc4122(),
                'sequence' => $streamEvent->getSequence(),
                'allSequence' => $streamEvent->getAllSequence(),
                'updatedAt' => $streamEvent->updatedAt()->format('Y-m-d H:i:s'),
                'tombstoned' => $streamEvent->isTombstoned(),
                'personalDataHasBeenEncrypted' => $streamEvent->hasBeenEncrypted(),
            ],
            [
                'tombstoned' => ParameterType::BOOLEAN,
                'personalDataHasBeenEncrypted' => ParameterType::BOOLEAN,
            ]
        );

        foreach ($streamEvent->getProperties() as $property) {
            $this->connection->executeStatement(
                <<<'SQL'
                    UPDATE stream_event_property
                    SET
                        serialized_value = :serializedValue,
                        updated_at = :updatedAt
                    WHERE stream_event_id = :streamEventId AND id = :id
                SQL,
                [
                    'streamEventId' => $streamEvent->getId()->toRfc4122(),
                    'id' => $property->getId(),
                    'serializedValue' => $property->getSerializedValue(),
                    'updatedAt' => $streamEvent->updatedAt()->format('Y-m-d H:i:s'),
                ]
            );
        }

        return $streamEvent;
    }

    public function find(StreamEventId $id): ?StreamEvent
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function paginated(StreamId $streamId, int $start, int $limit, bool $descending = true): array
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function withStreamIdPaginated(
        StreamId $id,
        int $start,
        int $limit,
        ?int $afterSequence = null,
        ?bool $ascending = false,
    ): \Countable&\IteratorAggregate {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function all(): array
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function allIterable(): iterable
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function allIterableRaw(?\DateTimeImmutable $startDate = null, ?\DateTimeImmutable $endDate = null): iterable
    {
        $where = ['se.tombstoned = false', 'NOT e.system_event'];
        $params = [];

        if ($startDate !== null) {
            $where[] = 'se.created_at >= :startDate';
            $params['startDate'] = $startDate->format('Y-m-d H:i:s');
        }

        if ($endDate !== null) {
            $where[] = 'se.created_at <= :endDate';
            $params['endDate'] = $endDate->format('Y-m-d H:i:s');
        }

        $whereClause = implode(' AND ', $where);

        $sql = <<<SQL
            SELECT se.id, se.event_id, se.stream_id, se.all_sequence, se.personal_data_has_been_encrypted,
                   sep.event_property_id, sep.name AS prop_name, sep.serialized_value AS prop_value,
                   e.name AS event_name, e.version AS event_version, se.created_at AS recorded_at
            FROM stream_event se
            JOIN event e ON e.id = se.event_id
            LEFT JOIN stream_event_property sep ON sep.stream_event_id = se.id
            WHERE $whereClause
            ORDER BY se.all_sequence ASC
        SQL;

        $current = null;
        $props   = [];

        foreach ($this->connection->executeQuery($sql, $params)->iterateAssociative() as $row) {
            if ($current === null) {
                $current = $row;
                $props   = [];
            } elseif ($row['id'] !== $current['id']) {
                $current['properties'] = $props;
                yield $current;
                $current = $row;
                $props   = [];
            }

            if ($row['event_property_id'] !== null) {
                $props[] = [
                    'event_property_id' => $row['event_property_id'],
                    'prop_name'         => $row['prop_name'],
                    'prop_value'        => $row['prop_value'],
                    'event_name'        => $row['event_name'],
                    'event_version'     => $row['event_version'],
                    'recorded_at'       => $row['recorded_at'],
                    'stream_id'         => $row['stream_id'],
                ];
            }
        }

        if ($current !== null) {
            $current['properties'] = $props;
            yield $current;
        }
    }

    public function allAfter(Checkpoint $checkpoint): iterable
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function paginatedIterable(StreamId $streamId, int $start, int $max): iterable
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function nextSequence(StreamId $streamId): int
    {
        $sql = <<<SQL
            SELECT COALESCE(MAX(sequence), 0) + 1
            FROM stream_event
            WHERE stream_id = :streamId
        SQL;

        return (int) $this->connection->executeQuery($sql, [
            'streamId' => $streamId->toString(),
        ])->fetchOne();
    }

    public function nextAllSequence(): int
    {
        $sql = <<<SQL
            SELECT COALESCE(MAX(all_sequence), 0) + 1
            FROM stream_event
        SQL;

        return (int) $this->connection->executeQuery($sql)->fetchOne();
    }

    public function fetchOneExcludingStreamIds(Checkpoint $checkpoint, array $streamIds): ?StreamEvent
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function tombstoneEvents(): void
    {
        throw new \BadMethodCallException('Not implemented.');
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

        return Checkpoint::fromInt($checkpoint);    }

    public function oldestUnworkedEvent(
        ApplicationId $applicationId,
        iterable $excludeStreamIds,
        Checkpoint $lastProcessedAllStreamCheckpoint,
        ?StreamId $drainStreamId = null,
        ?Checkpoint $afterCheckpoint = null,
    ): ?StreamEvent {
        $excludeStreamIdsArray = [];

        foreach ($excludeStreamIds as $excludeStreamId) {
            $excludeStreamIdsArray[] = $excludeStreamId->toString();
        }

        $startSql = <<<SQL
            SELECT se.*
            FROM stream_event se
            JOIN event e ON e.id = se.event_id
            LEFT JOIN application_checkpoint ac ON (ac.stream_id = se.stream_id AND ac.application_id = :applicationId)
            WHERE (ac.checkpoint < se.sequence OR ac.checkpoint IS NULL)
            AND e.system_event = false
            AND se.all_sequence != :lastProcessedAllStreamCheckpoint
        SQL;

        if ($drainStreamId && $afterCheckpoint) {
            $startSql .= ' AND se.stream_id = :drainStreamId AND se.sequence > :afterCheckpoint';
        } else if (!empty($excludeStreamIdsArray)) {
            $placeholders = implode(', ', array_map(static fn ($id) => '\'' . addslashes($id) . '\'', $excludeStreamIdsArray));
            $startSql .= ' AND se.stream_id NOT IN (' . $placeholders . ')';
        }

        $endSql = <<<SQL
            ORDER BY se.all_sequence ASC
        SQL;

        $params = [
            'applicationId' => $applicationId->toString(),
            'lastProcessedAllStreamCheckpoint' => $lastProcessedAllStreamCheckpoint->toInt(),
        ];

        if ($drainStreamId && $afterCheckpoint) {
            $params['drainStreamId'] = $drainStreamId->toString();
            $params['afterCheckpoint'] = $afterCheckpoint->toInt();
        }

        $event = $this->connection->executeQuery($startSql . $endSql, $params)->fetchAssociative();

        if (false === $event) {
            return null;
        }

        $createdAt = new \DateTimeImmutable($event['created_at']);

        return StreamEvent::fromRow(
            StreamEventId::fromString($event['id']),
            EventId::fromString($event['event_id']),
            StreamId::fromString($event['stream_id']),
            EventName::fromString($event['event_name']),
            EventVersion::fromInt($event['event_version']),
            new ArrayCollection(),
            Stream::create(
                StreamId::fromString($event['stream_id']),
                $createdAt,
            ),
            $createdAt,
            (int) $event['sequence'],
            (int) $event['all_sequence'],
        );
    }

    public function withStreamId(StreamId $id, int $start, ?int $end = null): iterable
    {
        throw new \BadMethodCallException('Not implemented.');
    }

    public function eventsWithPersonalDataNotEncryptedBefore(\DateTimeImmutable $before, ?int $limit = null): iterable
    {
        $sql = <<<SQL
            SELECT DISTINCT se.*
            FROM stream_event se
            JOIN event_property ep ON se.event_id = ep.event_id
            WHERE se.personal_data_has_been_encrypted = false
            AND ep.contains_personal_data = true
            AND se.created_at <= :before
        SQL;

        if ($limit !== null) {
            $sql .= ' LIMIT ' . $limit;
        }

        $rows = $this->connection->fetchAllAssociative($sql, [
            'before' => $before->format('Y-m-d H:i:s'),
        ]);

        return array_map(function (array $row) {
            $streamEvent = StreamEvent::fromRow(
                StreamEventId::fromString($row['id']),
                EventId::fromString($row['event_id']),
                StreamId::fromString($row['stream_id']),
                EventName::fromString($row['event_name']),
                EventVersion::fromInt((int) $row['event_version']),
                new ArrayCollection(),
                Stream::create(StreamId::fromString($row['stream_id']), new \DateTimeImmutable($row['created_at'])), // Minimal stream
                new \DateTimeImmutable($row['created_at']),
                (int) $row['sequence'],
                (int) $row['all_sequence'],
            );
            $streamEvent->setPersonalDataHasBeenEncrypted((bool) $row['personal_data_has_been_encrypted']);

            // Fetch properties
            $propSql = 'SELECT * FROM stream_event_property WHERE stream_event_id = :id';
            $propRows = $this->connection->fetchAllAssociative($propSql, ['id' => $row['id']]);
            $properties = new ArrayCollection();
            foreach ($propRows as $propRow) {
                $property = new StreamEventProperty();
                
                // We need to use reflection or access private properties since there are no setters for everything
                $reflection = new \ReflectionClass(StreamEventProperty::class);
                
                $idProp = $reflection->getProperty('id');
                $idProp->setAccessible(true);
                $idProp->setValue($property, (int) $propRow['id']);

                $nameProp = $reflection->getProperty('name');
                $nameProp->setAccessible(true);
                $nameProp->setValue($property, $propRow['name']);

                $typeProp = $reflection->getProperty('type');
                $typeProp->setAccessible(true);
                $typeProp->setValue($property, $propRow['type']);

                $valProp = $reflection->getProperty('serializedValue');
                $valProp->setAccessible(true);
                $valProp->setValue($property, $propRow['serialized_value']);

                $epIdProp = $reflection->getProperty('eventPropertyId');
                $epIdProp->setAccessible(true);
                $epIdProp->setValue($property, Uuid::fromString($propRow['event_property_id']));

                $property->setStreamEvent($streamEvent);
                $properties->add($property);
            }
            $streamEvent->setProperties($properties);

            return $streamEvent;
        }, $rows);
    }

    public function countEventsWithPersonalDataNotEncryptedBefore(\DateTimeImmutable $before): int
    {
        $sql = <<<SQL
            SELECT COUNT(DISTINCT se.id)
            FROM stream_event se
            JOIN event_property ep ON se.event_id = ep.event_id
            WHERE se.personal_data_has_been_encrypted = false
            AND ep.contains_personal_data = true
            AND se.created_at <= :before
        SQL;

        return (int) $this->connection->fetchOne($sql, [
            'before' => $before->format('Y-m-d H:i:s'),
        ]);
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

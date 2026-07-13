<?php

declare(strict_types=1);

namespace App\Repository\Postgres;

use App\Domain\Event\Exception\NoEventFound;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventVersion;
use App\Domain\Event\Repository\EventRepository;
use App\Entity\Event;
use App\Entity\EventProperty;
use App\Domain\Event\Model\EventProperty as EventPropertyModel;
use App\Domain\Event\Model\EventPropertyId;
use App\Domain\Event\Model\EventPropertyName;
use App\Extension\Default\PropertyType\Json;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Psr\Clock\ClockInterface;

final class PostgresEventRepository implements EventRepository
{
    public function __construct(
        private Connection $connection,
        private ClockInterface $clock,
    ) {}

    public function create(Event $event): Event
    {
        $this->connection->executeStatement(
            <<<SQL
                INSERT INTO event (
                    id, name, version, retired, created_at, updated_at,
                    system_event, deleted, tombstone_after_seconds
                ) VALUES (
                    :id, :name, :version, :retired, :createdAt, :updatedAt,
                    :systemEvent, :deleted, :tombstoneAfterSeconds
                )
            SQL,
            [
                'id'                    => $event->getId()->toString(),
                'name'                  => $event->getName(),
                'version'               => $event->getVersion(),
                'retired'               => $event->isRetired(),
                'createdAt'             => $event->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt'             => $event->getUpdatedAt()->format('Y-m-d H:i:s'),
                'systemEvent'           => $event->isSystemEvent(),
                'deleted'               => $event->isDeleted(),
                'tombstoneAfterSeconds' => $event->getTombstoneAfterSeconds(),
            ],
            [
                'retired'     => ParameterType::BOOLEAN,
                'systemEvent' => ParameterType::BOOLEAN,
                'deleted'     => ParameterType::BOOLEAN,
            ]
        );

        foreach ($event->getProperties() as $property) {
            $this->connection->executeStatement(
                <<<SQL
                    INSERT INTO event_property (
                        id, name, type, type_class, event_id, created_at, updated_at, contains_personal_data
                    ) VALUES (
                        :id, :name, :type, :typeClass, :eventId, :createdAt, :updatedAt, :containsPersonalData
                    )
                SQL,
                [
                    'id'                 => $property->id()->toRfc4122(),
                    'name'               => $property->getName(),
                    'type'               => $property->type(),
                    'typeClass'          => $property->getTypeClass(),
                    'eventId'            => $event->getId()->toString(),
                    'createdAt'          => $property->createdAt()->format('Y-m-d H:i:s'),
                    'updatedAt'          => $property->updatedAt()->format('Y-m-d H:i:s'),
                    'containsPersonalData' => $property->hasPersonalData(),
                ],
                [
                    'containsPersonalData' => ParameterType::BOOLEAN,
                ]
            );
        }

        return $event;
    }

    public function update(Event $event): Event
    {
        $this->connection->executeStatement(
            <<<SQL
                UPDATE event SET
                    name = :name,
                    version = :version,
                    retired = :retired,
                    updated_at = :updatedAt,
                    system_event = :systemEvent,
                    deleted = :deleted,
                    tombstone_after_seconds = :tombstoneAfterSeconds
                WHERE id = :id
            SQL,
            [
                'id'                    => $event->getId()->toString(),
                'name'                  => $event->getName(),
                'version'               => $event->getVersion(),
                'retired'               => $event->isRetired(),
                'updatedAt'             => $event->getUpdatedAt()->format('Y-m-d H:i:s'),
                'systemEvent'           => $event->isSystemEvent(),
                'deleted'               => $event->isDeleted(),
                'tombstoneAfterSeconds' => $event->getTombstoneAfterSeconds(),
            ],
            [
                'retired'     => ParameterType::BOOLEAN,
                'systemEvent' => ParameterType::BOOLEAN,
                'deleted'     => ParameterType::BOOLEAN,
            ]
        );

        return $event;
    }

    public function find(EventId $id): ?Event
    {
        if ($id->isAny()) {
            return $this->eventRepresentingAll();
        }

        $row = $this->connection->fetchAssociative(
            'SELECT * FROM event WHERE id = :id',
            ['id' => $id->toString()]
        );

        if (false === $row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findStrict(EventId $id): Event
    {
        return $this->find($id) ?? throw NoEventFound::withId($id);
    }

    public function findByNameStrict(EventName $name, EventVersion $version): Event
    {
        if ($name->sameAs(EventName::any())) {
            return $this->eventRepresentingAll();
        }

        $row = $this->connection->fetchAssociative(
            'SELECT * FROM event WHERE name = :name AND version = :version AND deleted = false',
            [
                'name'    => $name->toString(),
                'version' => $version->toInt(),
            ]
        );

        if (false === $row) {
            throw NoEventFound::withName($name);
        }

        return $this->hydrate($row);
    }

    public function all(): array
    {
        $rows = $this->connection->fetchAllAssociative('SELECT * FROM event');

        return array_map(fn (array $row): Event => $this->hydrate($row), $rows);
    }

    public function allNonSystem(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM event WHERE system_event = false'
        );

        return array_map(fn (array $row): Event => $this->hydrate($row), $rows);
    }

    public function findByEventIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $params = array_map(static fn (EventId $id): string => $id->toString(), $ids);

        $rows = $this->connection->fetchAllAssociative(
            "SELECT * FROM event WHERE id IN ($placeholders)",
            $params
        );

        return array_map(fn (array $row): Event => $this->hydrate($row), $rows);
    }

    public function paginated(int $start, int $max, ?string $search = null): \Countable&\IteratorAggregate
    {
        $sql = 'SELECT * FROM event';
        $params = [];

        if (null !== $search) {
            $sql .= ' WHERE LOWER(name) LIKE :search';
            $params['search'] = '%' . strtolower($search) . '%';
        }

        $sql .= ' LIMIT :max OFFSET :start';
        $params['max'] = $max;
        $params['start'] = $start;

        $rows = $this->connection->fetchAllAssociative($sql, $params, [
            'max'   => ParameterType::INTEGER,
            'start' => ParameterType::INTEGER,
        ]);

        $countSql = 'SELECT COUNT(*) FROM event';
        $countParams = [];
        if (null !== $search) {
            $countSql .= ' WHERE LOWER(name) LIKE :search';
            $countParams['search'] = '%' . strtolower($search) . '%';
        }

        $total = (int) $this->connection->fetchOne($countSql, $countParams);
        $events = array_map(fn (array $row): Event => $this->hydrate($row), $rows);

        return new class($events, $total) implements \Countable, \IteratorAggregate {
            /** @param Event[] $events */
            public function __construct(
                private array $events,
                private int $total,
            ) {}

            public function count(): int
            {
                return $this->total;
            }

            /**
             * @return \ArrayIterator<int, Event>
             */
            public function getIterator(): \ArrayIterator
            {
                return new \ArrayIterator($this->events);
            }
        };
    }

    public function allPersonalDataPropertyIds(): array
    {
        $allEvents = $this->all();
        $eventPropertiesWithPersonalData = [];

        foreach ($allEvents as $eventEntity) {
            $propertyIds = [];
            foreach ($eventEntity->getProperties() as $eventProperty) {
                if ($eventProperty->hasPersonalData()) {
                    $propertyIds[] = $eventProperty->id()->toRfc4122();
                }
            }
            if (!empty($propertyIds)) {
                $eventPropertiesWithPersonalData[$eventEntity->getId()->toString()] = $propertyIds;
            }
        }

        return $eventPropertiesWithPersonalData;
    }

    public function eventRepresentingAll(): Event
    {
        $now = $this->clock->now();
        $event = Event::create(
            EventId::any(),
            'all',
            0,
            $now,
        );

        return $event->setProperties(
            new ArrayCollection([
                EventProperty::create(
                    new EventPropertyModel(
                        EventPropertyId::metadata(),
                        EventPropertyName::fromString('Metadata'),
                        Json::create(),
                        false,
                    ),
                    $now,
                )
            ])
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Event
    {
        $event = Event::create(
            EventId::fromString((string) $row['id']),
            (string) $row['name'],
            (int) $row['tombstone_after_seconds'],
            new \DateTimeImmutable((string) $row['created_at']),
        );

        $event->setUpdatedAt(new \DateTimeImmutable((string) $row['updated_at']));
        $event->setRetired((bool) $row['retired']);
        $event->setIsSystemEvent((bool) $row['system_event']);
        $event->setTombstoneAfterSeconds((int) $row['tombstone_after_seconds']);

        if ($row['deleted']) {
            $ref = new \ReflectionProperty(Event::class, 'deleted');
            $ref->setValue($event, true);
        }

        if ($row['version'] !== 1) {
            $ref = new \ReflectionProperty(Event::class, 'version');
            $ref->setValue($event, (int) $row['version']);
        }

        $propertyRows = $this->connection->fetchAllAssociative(
            'SELECT * FROM event_property WHERE event_id = :eventId',
            ['eventId' => (string) $row['id']]
        );

        $properties = array_map(static function (array $propRow): EventProperty {
            $property = EventProperty::create(
                new EventPropertyModel(
                    EventPropertyId::fromString((string) $propRow['id']),
                    EventPropertyName::fromString((string) $propRow['name']),
                    $propRow['type_class']::create(),
                    (bool) $propRow['contains_personal_data'],
                ),
                new \DateTimeImmutable((string) $propRow['created_at']),
            );
            $property->setUpdatedAt(new \DateTimeImmutable((string) $propRow['updated_at']));
            return $property;
        }, $propertyRows);

        $event->setProperties(new ArrayCollection($properties));

        return $event;
    }
}

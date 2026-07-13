<?php

namespace App\Repository\Postgres;

use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventVersion;
use App\Domain\Event\Repository\EventWriterRepository;
use Doctrine\DBAL\Connection;

final readonly class PostgresEventWriterRepository implements EventWriterRepository
{
    public function __construct(private Connection $connection) {}

    public function eventPropertiesForEventWithId(EventId $eventId): array
    {
        $sql = <<<SQL
            SELECT *
            FROM event_property
            WHERE event_id = :eventId
        SQL;

        $query = $this->connection->prepare($sql);

        $query->bindValue('eventId', $eventId->toString());

        return array_reduce($query->executeQuery()->fetchAllAssociative(), static function (array $carry, $property) {
            $carry[$property['name']] = $property;

            return $carry;
        }, []);
    }

    /**
     * @return array{
     *      id: string,
     *      name: string,
     *      version: int,
     *      retired: bool,
     *      created_at: string,
     *      updated_at: string,
     *      system_event: bool,
     *      deleted: bool,
     *      tombstone_after_seconds:  int,
     * }|null
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function eventWithNameAndVersion(EventName $eventName, EventVersion $version): ?array
    {
        $sql = <<<SQL
            SELECT *
            FROM event
            WHERE name = :name
            AND version = :version
        SQL;

        $query = $this->connection->prepare($sql);

        $query->bindValue('name', $eventName->toString());
        $query->bindValue('version', $version->toInt());

        $result = $query->executeQuery()->fetchAssociative();

        if (false === $result) {
            return null;
        }

        return $result;
    }

    /**
     * @return array<string, string[]>
     */
    public function allPersonalDataPropertyIds(): array
    {
        $sql = <<<SQL
            SELECT event_id, id
            FROM event_property
            WHERE contains_personal_data = true
        SQL;

        $rows = $this->connection->fetchAllAssociative($sql);

        $eventPropertiesWithPersonalData = [];
        foreach ($rows as $row) {
            $eventId = (string) $row['event_id'];
            if (!isset($eventPropertiesWithPersonalData[$eventId])) {
                $eventPropertiesWithPersonalData[$eventId] = [];
            }
            $eventPropertiesWithPersonalData[$eventId][] = (string) $row['id'];
        }

        return $eventPropertiesWithPersonalData;
    }
}

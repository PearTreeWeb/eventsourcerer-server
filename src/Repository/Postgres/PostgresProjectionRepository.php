<?php

declare(strict_types=1);

namespace App\Repository\Postgres;

use App\Domain\Event\Model\EventId;
use App\Domain\Projection\Exception\ProjectionDoesNotExist;
use App\Domain\Projection\Model\ProjectionCondition;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionName;
use App\Domain\Projection\Repository\ProjectionRepository;
use App\Entity\Projection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

final class PostgresProjectionRepository implements ProjectionRepository
{
    public function __construct(private readonly Connection $connection)  {}

    public function update(Projection $projection, \DateTimeImmutable $now): Projection
    {
        $projection->setUpdatedAt($now);

        $this->connection->executeStatement(
            <<<SQL
                UPDATE projection SET
                    name = :name,
                    partition = :partition,
                    continuous = :continuous,
                    deleted = :deleted,
                    condition = :condition,
                    current_state = :currentState,
                    total_number_of_events_processed = :totalNumberOfEventsProcessed,
                    reset_total_number_of_events_processed = :resetTotalNumberOfEventsProcessed,
                    last_all_sequence_checkpoint_processed = :lastAllSequenceCheckpointProcessed,
                    expose_state_via_api = :exposeStateViaApi,
                    updated_at = :updatedAt
                WHERE id = :id
            SQL,
            [
                'id' => $projection->getIdAsString(),
                'name' => $projection->getName(),
                'partition' => $projection->isPartitioned(),
                'continuous' => $projection->isContinuous(),
                'deleted' => $projection->isDeleted(),
                'condition' => $projection->getCondition(),
                'currentState' => json_encode($projection->getCurrentState()),
                'totalNumberOfEventsProcessed' => $projection->getTotalNumberOfEventsProcessed(),
                'resetTotalNumberOfEventsProcessed' => $projection->getResetTotalNumberOfEventsProcessed(),
                'lastAllSequenceCheckpointProcessed' => $projection->getLastAllSequenceCheckpointProcessed(),
                'exposeStateViaApi' => $projection->getExposeStateViaApi(),
                'updatedAt' => $projection->getUpdatedAt()->format('Y-m-d H:i:s'),
            ],
            [
                'partition' => ParameterType::BOOLEAN,
                'continuous' => ParameterType::BOOLEAN,
                'deleted' => ParameterType::BOOLEAN,
                'exposeStateViaApi' => ParameterType::BOOLEAN,
            ]
        );

        return $projection;
    }

    public function create(Projection $projection): Projection
    {
        $this->connection->executeStatement(
            <<<SQL
                INSERT INTO projection (
                    id, name, partition, continuous, deleted, condition, current_state,
                    total_number_of_events_processed, reset_total_number_of_events_processed,
                    last_all_sequence_checkpoint_processed, expose_state_via_api,
                    created_at, updated_at
                ) VALUES (
                    :id, :name, :partition, :continuous, :deleted, :condition, :currentState,
                    :totalNumberOfEventsProcessed, :resetTotalNumberOfEventsProcessed,
                    :lastAllSequenceCheckpointProcessed, :exposeStateViaApi,
                    :createdAt, :updatedAt
                )
            SQL,
            [
                'id' => $projection->getIdAsString(),
                'name' => $projection->getName(),
                'partition' => $projection->isPartitioned(),
                'continuous' => $projection->isContinuous(),
                'deleted' => $projection->isDeleted(),
                'condition' => $projection->getCondition(),
                'currentState' => json_encode($projection->getCurrentState()),
                'totalNumberOfEventsProcessed' => $projection->getTotalNumberOfEventsProcessed(),
                'resetTotalNumberOfEventsProcessed' => $projection->getResetTotalNumberOfEventsProcessed(),
                'lastAllSequenceCheckpointProcessed' => $projection->getLastAllSequenceCheckpointProcessed(),
                'exposeStateViaApi' => $projection->getExposeStateViaApi(),
                'createdAt' => $projection->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $projection->getUpdatedAt()->format('Y-m-d H:i:s'),
            ],
            [
                'partition' => ParameterType::BOOLEAN,
                'continuous' => ParameterType::BOOLEAN,
                'deleted' => ParameterType::BOOLEAN,
                'exposeStateViaApi' => ParameterType::BOOLEAN,
            ]
        );

        return $projection;
    }

    public function delete(Projection $projection): void
    {
        $this->connection->executeStatement(
            'DELETE FROM projection WHERE id = :id',
            ['id' => $projection->getIdAsString()]
        );
    }

    public function find(ProjectionId $id): Projection
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM projection WHERE id = :id',
            ['id' => $id->toString()]
        );

        if (false === $row) {
            throw ProjectionDoesNotExist::withName($id->toString());
        }

        return $this->hydrate($row);
    }

    public function findByNameStrict(ProjectionName $name): Projection
    {
        return $this->findByName($name)
            ?? throw ProjectionDoesNotExist::withName($name->toString());
    }

    public function findByName(ProjectionName $name): ?Projection
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM projection WHERE name = :name',
            ['name' => $name->toString()]
        );

        if (false === $row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function all(): array
    {
        $rows = $this->connection->fetchAllAssociative('SELECT * FROM projection');

        return array_map(fn(array $row): Projection => $this->hydrate($row), $rows);
    }

    public function doesNotExist(ProjectionName $name): bool
    {
        return null === $this->findByName($name);
    }

    public function updateConditionGroups(Projection $projection, \DateTimeImmutable $now): void
    {
        $this->connection->executeStatement(
            <<<SQL
                UPDATE projection_mutation
                SET condition_groups = '[]'
                WHERE projection_id = :projectionId
            SQL,
            ['projectionId' => $projection->getIdAsString()]
        );
    }

    public function maxAllSequenceForProjection(ProjectionId $id): int
    {
        $sql = <<<SQL
            SELECT CASE
                WHEN EXISTS (
                    SELECT 1
                    FROM projection_mutation pm_all
                    JOIN event e ON e.id = se.event_id
                    WHERE pm_all.projection_id = :projectionId
                    AND pm_all.event_id = :allEventId
                    AND NOT e.system_event
                ) THEN (
                    SELECT MAX(se_all.all_sequence) 
                    FROM stream_event se_all
                    JOIN event e ON e.id = se_all.event_id
                    WHERE NOT e.system_event
                )
                ELSE (
                    SELECT MAX(se.all_sequence)
                    FROM stream_event se
                    JOIN event e ON e.id = se.event_id
                    WHERE se.event_id IN (
                        SELECT pm.event_id
                        FROM projection_mutation pm
                        WHERE pm.projection_id = :projectionId
                    )
                    AND NOT e.system_event
                )
            END AS max_all_sequence
        SQL;

        $result = $this->connection->executeQuery($sql, [
            'projectionId' => $id->toString(),
            'allEventId' => EventId::any(),
        ]);

        return $result->fetchOne() ?? 0;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Projection
    {
        $projection = Projection::create(
            ProjectionId::fromString((string) $row['id']),
            ProjectionName::fromString((string) $row['name']),
            (bool) $row['continuous'],
            (bool) $row['partition'],
            (bool) $row['expose_state_via_api'],
            new \DateTimeImmutable((string)$row['created_at']),
        );

        $projection->setUpdatedAt(new \DateTimeImmutable((string) $row['updated_at']));
        $projection->setCondition((string) $row['condition']);
        $projection->setCurrentState(json_decode((string) ($row['current_state'] ?? '[]'), true) ?? []);
        $projection->setTotalNumberOfEventsProcessed((int) $row['total_number_of_events_processed']);
        $projection->setResetTotalNumberOfEventsProcessed((int) $row['reset_total_number_of_events_processed']);
        $projection->setLastAllSequenceCheckpointProcessed((int) $row['last_all_sequence_checkpoint_processed']);

        if ($row['deleted']) {
            // Mark as deleted via reflection since there's no setter
            $ref = new \ReflectionProperty(Projection::class, 'deleted');
            $ref->setValue($projection, true);
        }

        return $projection;
    }

    public function resetProjection(Projection $projection, \DateTimeImmutable $resetAt): Projection
    {
        $projection = $projection
            ->setCondition(ProjectionCondition::Resetting->value)
            ->setCurrentState([])
            ->setLastAllSequenceCheckpointProcessed(0);

        $sql = <<<SQL
            UPDATE projection SET
              condition = :condition,
              current_state = :currentState,
              last_all_sequence_checkpoint_processed = 0,
              updated_at = :updatedAt
            WHERE id = :id
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'id' => $projection->getIdAsString(),
                'condition' => ProjectionCondition::Resetting->value,
                'currentState' => json_encode([]),
                'updatedAt' => $resetAt->format('Y-m-d H:i:s'),
            ]
        );
        return $projection;
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Postgres;

use App\Domain\Event\Model\EventId;
use App\Domain\Projection\Exception\ProjectionMutationDoesNotExist;
use App\Domain\Projection\Model\ConditionGroupAndOr;
use App\Domain\Projection\Model\ConditionParameterValues;
use App\Domain\Projection\Model\MutationType;
use App\Domain\Projection\Model\ProjectionEventPropertyId;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionMutationCondition;
use App\Domain\Projection\Model\ProjectionMutationConditionGroup;
use App\Domain\Projection\Model\ProjectionMutationConditionGroupKey;
use App\Domain\Projection\Model\ProjectionMutationConditionGroups;
use App\Domain\Projection\Model\ProjectionMutationId;
use App\Domain\Projection\Repository\ProjectionMutationRepository;
use App\Entity\MutationCondition;
use App\Entity\MutationConditionsGroup;
use App\Entity\ProjectionMutation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Illuminate\Support\Collection;
use Symfony\Component\Uid\Uuid;

final readonly class PostgresProjectionMutationRepository implements ProjectionMutationRepository
{
    public function __construct(private Connection $connection) {}

    public function byIdStrict(ProjectionMutationId $id): ProjectionMutation
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM projection_mutation WHERE id = :id',
            ['id' => $id->toString()]
        );

        if ($row === false) {
            throw ProjectionMutationDoesNotExist::withId($id);
        }

        return $this->hydrate($row);
    }

    public function findAllWithEventId(EventId $id): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM projection_mutation WHERE event_id IN (:eventId, :anyId)',
            [
                'eventId' => $id->toString(),
                'anyId'   => EventId::any()->toString(),
            ]
        );

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function findAllWithProjectionId(ProjectionId $projectionId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM projection_mutation WHERE projection_id = :projectionId',
            ['projectionId' => $projectionId->toString()]
        );

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function findAllWithEventIdAndEventPropertyId(EventId $id, ProjectionEventPropertyId $eventPropertyId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM projection_mutation WHERE event_id = :eventId AND projection_event_property_id = :eventPropertyId',
            [
                'eventId'         => $id->toString(),
                'eventPropertyId' => $eventPropertyId->toString(),
            ]
        );

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function findAllWithEventIdAndProjectionId(EventId $id, ProjectionId $projectionId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM projection_mutation WHERE event_id IN (:eventId, :anyId) AND projection_id = :projectionId',
            [
                'eventId'      => $id->toString(),
                'anyId'        => EventId::any()->toString(),
                'projectionId' => $projectionId->toString(),
            ]
        );

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function findMutationCondition(int $mutationConditionId): MutationCondition
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM mutation_condition WHERE id = :id',
            ['id' => $mutationConditionId]
        );

        if ($row === false) {
            throw new \RuntimeException(sprintf('MutationCondition with id "%d" does not exist', $mutationConditionId));
        }

        return $this->hydrateMutationCondition($row);
    }

    public function removeConditionGroup(MutationConditionsGroup $mutationConditionsGroup): void
    {
        $this->connection->executeStatement(
            'DELETE FROM mutation_condition WHERE conditions_group_id = :groupId',
            ['groupId' => $mutationConditionsGroup->getId()]
        );

        $this->connection->executeStatement(
            'DELETE FROM mutation_conditions_group WHERE id = :id',
            ['id' => $mutationConditionsGroup->getId()]
        );
    }

    public function removeCondition(MutationCondition $condition): void
    {
        $this->connection->executeStatement(
            'DELETE FROM mutation_condition WHERE id = :id',
            ['id' => $condition->getId()]
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): ProjectionMutation
    {
        $mutation = new ProjectionMutation();

        $ref = new \ReflectionClass($mutation);

        $this->setProperty($ref, $mutation, 'id', Uuid::fromString($row['id']));
        $this->setProperty($ref, $mutation, 'eventId', Uuid::fromString($row['event_id']));
        $this->setProperty($ref, $mutation, 'projectionId', Uuid::fromString($row['projection_id']));
        $this->setProperty($ref, $mutation, 'eventPropertyId', Uuid::fromString($row['event_property_id']));
        $this->setProperty($ref, $mutation, 'projectionEventPropertyId', Uuid::fromString($row['projection_event_property_id']));
        $this->setProperty($ref, $mutation, 'type', $row['type']);
        $this->setProperty($ref, $mutation, 'createdAt', new \DateTimeImmutable($row['created_at']));
        $this->setProperty($ref, $mutation, 'updatedAt', new \DateTimeImmutable($row['updated_at']));
        $this->setProperty($ref, $mutation, 'conditionGroups', new ArrayCollection($this->hydrateConditionGroups($mutation)));

        return $mutation;
    }

    /**
     * @return array<MutationConditionsGroup>
     */
    private function hydrateConditionGroups(ProjectionMutation $mutation): array
    {
        $groupRows = $this->connection->fetchAllAssociative(
            'SELECT * FROM mutation_conditions_group WHERE projection_mutation_id = :mutationId',
            ['mutationId' => $mutation->getId()->toRfc4122()]
        );

        return array_map(function (array $groupRow) use ($mutation): MutationConditionsGroup {
            $group = new MutationConditionsGroup();
            $groupRef = new \ReflectionClass($group);

            $this->setProperty($groupRef, $group, 'id', (int) $groupRow['id']);
            $this->setProperty($groupRef, $group, 'groupType', $groupRow['group_type']);
            $this->setProperty($groupRef, $group, 'projectionMutation', $mutation);

            $conditionRows = $this->connection->fetchAllAssociative(
                'SELECT * FROM mutation_condition WHERE conditions_group_id = :groupId',
                ['groupId' => $groupRow['id']]
            );

            $conditions = array_map(fn (array $cr) => $this->hydrateMutationCondition($cr, $group), $conditionRows);
            $this->setProperty($groupRef, $group, 'conditionsGroup', new ArrayCollection($conditions));

            return $group;
        }, $groupRows);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrateMutationCondition(array $row, ?MutationConditionsGroup $group = null): MutationCondition
    {
        $condition = new MutationCondition();
        $ref = new \ReflectionClass($condition);

        $this->setProperty($ref, $condition, 'id', (int) $row['id']);
        $this->setProperty($ref, $condition, 'type', $row['type']);
        $parameterValues = $row['parameter_values'] ?? null;
        if (is_string($parameterValues)) {
            $parameterValues = json_decode($parameterValues, true);
        }
        $this->setProperty($ref, $condition, 'parameterValues', (array) $parameterValues);

        if ($group !== null) {
            $this->setProperty($ref, $condition, 'conditionsGroup', $group);
        }

        return $condition;
    }

    /**
     * @template T of object
     * @param \ReflectionClass<T> $ref
     * @param T $object
     */
    private function setProperty(\ReflectionClass $ref, object $object, string $property, mixed $value): void
    {
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }

    public function findAllForEvent(EventId $eventId, ProjectionId $projectionId): array
    {
        $sql = <<<SQL
            SELECT pm.id, pm.event_property_id, pm.projection_property_id, pm.type,
                   pp.name AS projection_property_name, pp.type AS projection_property_type
            FROM projection_mutation pm
            JOIN projection_property pp ON pp.id = pm.projection_property_id
            WHERE (pm.event_id = :eventId OR pm.event_id = :anyEvent)
            AND pm.projection_id = :projectionId
        SQL;

        $result = $this->connection->executeQuery($sql, [
            'eventId'      => $eventId->toString(),
            'anyEvent'     => EventId::any()->toString(),
            'projectionId' => $projectionId->toString(),
        ]);

        return $result->fetchAllAssociative();
    }

    public function conditionGroupsForProjectionMutationWithId(
        ProjectionMutationId $id
    ): ProjectionMutationConditionGroups {
        $sql = <<<SQL
            SELECT mcg.id, mcg.group_type, mc.type, mc.parameter_values
            FROM mutation_conditions_group mcg
            LEFT JOIN mutation_condition mc ON mc.conditions_group_id = mcg.id
            WHERE projection_mutation_id = :id
        SQL;

        $result = $this->connection->executeQuery($sql, [
            'id' => $id->toString(),
        ]);

        $rows = $result->fetchAllAssociative();

        $grouped = collect($rows)
            ->groupBy(fn (array $row) => $row['id'])
            ->map(function (Collection $groupRows) {
                $groupType = ConditionGroupAndOr::from($groupRows->first()['group_type']);

                $conditions = $groupRows
                    ->filter(fn (array $row) => $row['type'] !== null && $row['parameter_values'] !== null)
                    ->map(fn (array $row) => new ProjectionMutationCondition(
                        ProjectionMutationConditionGroupKey::fromInt($row['id']),
                        MutationType::fromString($row['type']),
                        ConditionParameterValues::fromArray(is_string($row['parameter_values']) ? json_decode($row['parameter_values'], true) : (array) $row['parameter_values']),
                    ))
                    ->all();

                return ProjectionMutationConditionGroup::fromArray($conditions)
                    ->withGroupType($groupType);
            })
            ->all();

        return ProjectionMutationConditionGroups::fromArray($grouped);
    }
}

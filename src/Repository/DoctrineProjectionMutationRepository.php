<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Event\Model\EventId;
use App\Domain\Projection\Exception\ProjectionMutationDoesNotExist;
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
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Illuminate\Support\Collection;

final class DoctrineProjectionMutationRepository implements ProjectionMutationRepository
{
    /**
     * @var EntityRepository<ProjectionMutation>
     */
    private EntityRepository $repository;

    /**
     * @var EntityRepository<MutationCondition>
     */
    private EntityRepository $mutationConditionRepository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Connection $connection
    ) {
        $this->repository = $entityManager->getRepository(ProjectionMutation::class);
        $this->mutationConditionRepository = $this->entityManager->getRepository(MutationCondition::class);
    }

    public function findAllWithEventId(EventId $id): array
    {
        return \array_merge(
            $this->repository->findBy(['eventId' => $id->toString()]),
            $this->repository->findBy(['eventId' => EventId::any()->toString()]),
        );
    }

    public function findAllWithEventIdAndEventPropertyId(EventId $id, ProjectionEventPropertyId $eventPropertyId): array
    {
        return $this->repository->findBy([
            'eventId'                   => $id->toString(),
            'projectionEventPropertyId' => $eventPropertyId->toString(),
        ]);
    }

    public function findAllWithEventIdAndProjectionId(EventId $id, ProjectionId $projectionId): array
    {
        return $this
            ->repository
            ->createQueryBuilder('pm')
            ->where('pm.eventId IN (:eventIds)')
            ->andWhere('pm.projectionId = :projectionId')
            ->setParameter('eventIds', [$id->toString(), EventId::any()->toString()])
            ->setParameter('projectionId', $projectionId->toString())
            ->getQuery()
            ->getResult();
    }

    public function findAllWithProjectionId(ProjectionId $projectionId): array
    {
        return $this
            ->repository
            ->createQueryBuilder('pm')
            ->where('pm.projectionId = :projectionId')
            ->setParameter('projectionId', $projectionId->toString())
            ->getQuery()
            ->getResult();
    }

    public function byIdStrict(ProjectionMutationId $id): ProjectionMutation
    {
        return $this->repository->find($id->toString()) ?? throw ProjectionMutationDoesNotExist::withId($id);
    }

    public function removeConditionGroup(MutationConditionsGroup $mutationConditionsGroup): void
    {
        foreach ($mutationConditionsGroup->getConditionsGroup() as $mutationCondition) {
            $this->entityManager->remove($mutationCondition);
        }

        $this->entityManager->remove($mutationConditionsGroup);
        $this->entityManager->flush();
    }

    public function removeCondition(MutationCondition $condition): void
    {
        $this->entityManager->remove($condition);
        $this->entityManager->flush();
    }

    public function findMutationCondition(int $mutationConditionId): MutationCondition
    {
        return $this->mutationConditionRepository->find($mutationConditionId);
    }

    /**
     * @return array<array{id: string, event_property_id: string, projection_property_id: string, type: string, projection_property_name: string, projection_property_type: string}>
     */
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
            SELECT mcg.id, mcg.group_type, mc.type, mc.parameter_values, mc.event_property_id, mc.event_property_type
            FROM mutation_conditions_group mcg
            LEFT JOIN mutation_condition mc ON mc.conditions_group_id = mcg.id
            WHERE projection_mutation_id = :id
        SQL;

        $result = $this->connection->executeQuery($sql, [
            'id' => $id->toString(),
        ]);

        $rows = $result->fetchAllAssociative();

        $grouped = collect($rows)
            ->filter(fn (array $row) => $row['type'] !== null && $row['parameter_values'] !== null)
            ->map(fn (array $row) => new ProjectionMutationCondition(
                ProjectionMutationConditionGroupKey::fromInt($row['id']),
                MutationType::fromString($row['type']),
                ConditionParameterValues::fromArray(is_string($row['parameter_values']) ? json_decode($row['parameter_values'], true) : (array) $row['parameter_values']),
                $row['event_property_id'] ?? null,
                $row['event_property_type'] ?? null,
            )
            )
            ->groupBy(fn (ProjectionMutationCondition $condition) => $condition->key())
            ->map(fn (Collection $conditions) => ProjectionMutationConditionGroup::fromArray($conditions->all()))
            ->all();

        return ProjectionMutationConditionGroups::fromArray($grouped);
    }
}

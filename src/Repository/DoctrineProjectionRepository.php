<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Event\Model\EventId;
use App\Domain\Projection\Exception\ProjectionDoesNotExist;
use App\Domain\Projection\Model\ProjectionCondition;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionName;
use App\Domain\Projection\Repository\ProjectionRepository;
use App\Entity\Projection;
use App\Entity\ProjectionMutation;
use App\Entity\ProjectionProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class DoctrineProjectionRepository implements ProjectionRepository
{
    /**
     * @var EntityRepository<Projection>
     */
    private EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Projection::class);
    }

    public function create(Projection $projection): Projection
    {
        $this->entityManager->persist($projection);

        foreach ($projection->getProperties() as $property) {
            /** @var ProjectionProperty $property */
            $this->entityManager->persist($property);

            foreach ($property->getMutations() as $mutation) {
                /** @var ProjectionMutation $mutation */
                $this->entityManager->persist($mutation);
            }
        }

        $this->entityManager->flush();

        return $projection;
    }

    public function delete(Projection $projection): void
    {
        foreach ($projection->getProperties() as $property) {
            foreach ($property->getMutations() as $mutation) {
                $this->entityManager->remove($mutation);
            }

            $this->entityManager->remove($property);
        }

        $this->entityManager->remove($projection);
        $this->entityManager->flush();
    }

    public function find(ProjectionId $id): Projection
    {
        return $this->repository->find($id->toString());
    }

    public function all(): array
    {
        return $this->repository->findAll();
    }

    public function update(Projection $projection, \DateTimeImmutable $now): Projection
    {
        return $this->create($projection->setUpdatedAt($now));
    }

    public function findByNameStrict(ProjectionName $name): Projection
    {
        return $this->findByName($name)
            ?? throw ProjectionDoesNotExist::withName($name->toString());
    }

    public function findByName(ProjectionName $name): ?Projection
    {
        return $this->repository->findOneBy(['name' => $name->toString()]);
    }

    public function doesNotExist(ProjectionName $name): bool
    {
        return null === $this->findByName($name);
    }

    public function updateConditionGroups(Projection $projection, \DateTimeImmutable $now): void
    {
        $this->entityManager->persist($projection);

        foreach ($projection->getProperties() as $property) {
            foreach ($property->getMutations() as $mutation) {
                $mutation->setConditionGroups(new ArrayCollection());
                $this->entityManager->persist($mutation);
                $this->entityManager->flush();
            }
        }
    }

    public function maxAllSequenceForProjection(ProjectionId $id): int
    {
        $sql = <<<SQL
            SELECT CASE
                WHEN EXISTS (
                    SELECT 1
                    FROM projection_mutation pm_all
                    WHERE pm_all.projection_id = :projectionId
                    AND pm_all.event_id = :allEventId
                ) THEN (
                    SELECT MAX(se_all.all_sequence) 
                    FROM stream_event se_all
                    JOIN event e ON e.id = se_all.event_id
                    WHERE NOT e.system_event
                )
                ELSE (
                    SELECT MAX(se.all_sequence)
                    FROM stream_event se
                    WHERE se.event_id IN (
                        SELECT pm.event_id
                        FROM projection_mutation pm
                        WHERE pm.projection_id = :projectionId
                    )
                )
            END AS max_all_sequence
        SQL;

        $conn = $this->entityManager->getConnection();

        $result = $conn->executeQuery($sql, [
            'projectionId' => $id->toString(),
            'allEventId' => EventId::any(),
        ]);

        return $result->fetchOne() ?? 0;
    }

    public function resetProjection(Projection $projection, \DateTimeImmutable $resetAt): Projection
    {
        $projection = $projection
            ->setCondition(ProjectionCondition::Resetting->value)
            ->setCurrentState([])
            ->setLastAllSequenceCheckpointProcessed(0);

        $this->update($projection, $resetAt);

        return $projection;
    }
}

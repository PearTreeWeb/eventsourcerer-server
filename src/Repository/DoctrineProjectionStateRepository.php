<?php

namespace App\Repository;

use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionStateType;
use App\Domain\Projection\Repository\ProjectionStateRepository;
use App\Entity\ProjectionState;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

final class DoctrineProjectionStateRepository implements ProjectionStateRepository
{
    /**
     * @var EntityRepository<ProjectionState>
     */
    private EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(ProjectionState::class);
    }

    public function add(ProjectionState $projectionState): void
    {
        $this->entityManager->persist($projectionState);
        $this->entityManager->flush();
    }

    public function findByStreamAndProjectionId(
        StreamId $streamId,
        ProjectionStateType $type,
        ProjectionId $projectionId
    ): ?ProjectionState {
        return $this->repository->findOneBy([
            'streamId' => $streamId->toString(),
            'type' => $type->value,
            'projectionId' => $projectionId->toString(),
        ]);
    }

    public function findMasterByProjectionId(ProjectionId $projectionId): ?ProjectionState
    {
        return $this->repository->findOneBy([
            'streamId' => null,
            'projectionId' => $projectionId->toString(),
            'type' => ProjectionStateType::Main,
        ]);
    }

    public function findResetByProjectionId(ProjectionId $projectionId): ?ProjectionState
    {
        return $this->repository->findOneBy([
            'streamId' => null,
            'projectionId' => $projectionId->toString(),
            'type' => ProjectionStateType::Reset,
        ]);
    }

    public function update(ProjectionState $projectionState): void
    {
        $this->add($projectionState);
    }

    public function findAllCurrentWithProjectionId(ProjectionId $projectionId): array
    {
        return $this->repository->findBy([
            'projectionId' => $projectionId->toString(),
            'type' => ProjectionStateType::Main->value,
        ]);
    }

    public function delete(ProjectionState $projectionState): void
    {
        $this->entityManager->remove($projectionState);
        $this->entityManager->flush();
    }

    public function findAllResetStatesWithProjectionId(ProjectionId $projectionId): array
    {
        return $this->repository->findBy([
            'projectionId' => $projectionId->toString(),
            'type' => ProjectionStateType::Reset->value,
        ]);
    }
}

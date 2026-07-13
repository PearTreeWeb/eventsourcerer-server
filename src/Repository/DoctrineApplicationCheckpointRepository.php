<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Application\Repository\ApplicationCheckpointRepository;
use App\Entity\ApplicationCheckpoint;
use App\Entity\StreamEvent;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Psr\Clock\ClockInterface;

final class DoctrineApplicationCheckpointRepository implements ApplicationCheckpointRepository
{
    /**
     * @var EntityRepository<ApplicationCheckpoint>
     */
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock
    ) {
        $this->repository = $entityManager->getRepository(ApplicationCheckpoint::class);
    }

    public function create(ApplicationCheckpoint $applicationCheckpoint): ApplicationCheckpoint
    {
        $this->entityManager->persist($applicationCheckpoint);
        $this->entityManager->flush();

        return $applicationCheckpoint;
    }

    public function update(ApplicationCheckpoint $applicationCheckpoint): ApplicationCheckpoint
    {
        return $this->create($applicationCheckpoint);
    }

    public function all(): iterable
    {
        return $this->repository->findAll();
    }

    public function findOrCreate(ApplicationId $id, StreamId $streamId): ApplicationCheckpoint
    {
        return $this->repository->findOneBy([
           'applicationId' => $id->toString(),
           'streamId'      => $streamId->toString(),
        ]) ?? ApplicationCheckpoint::create(
            $id,
            $streamId,
            $this->clock->now()
        );
    }

    public function find(ApplicationId $id, StreamId $streamId): ApplicationCheckpoint
    {
        return $this->repository->findOneBy([
            'applicationId' => $id->toString(),
            'streamId'      => $streamId->toString(),
        ]);
    }

    /**
     * @return iterable<ApplicationCheckpoint>
     */
    public function byApplicationId(ApplicationId $id): iterable
    {
        return $this->repository->findBy([
            'applicationId' => $id,
        ]);
    }

    public function resetAllForApplicationWithId(ApplicationId $id): void
    {
        foreach ($this->byApplicationId($id) as $checkpoint) {
            $checkpoint->setCheckpoint(0);

            $this->entityManager->persist($checkpoint);
        }

        $this->entityManager->flush();
    }

    /**
     * @return iterable<array{streamId: string, maxSequence: int, checkpoint: int}>
     */
    public function byApplicationIdWithMaxSequences(ApplicationId $id): iterable
    {
        return $this
            ->repository
            ->createQueryBuilder('ac')
            ->select('ac.streamId', 'MAX(se.sequence) AS maxSequence', 'ac.checkpoint')
            ->join(StreamEvent::class, 'se', 'ON', 'se.streamId = ac.streamId')
            ->where('ac.applicationId = :applicationId')
            ->setParameter('applicationId', $id->toString())
            ->groupBy('ac.streamId', 'ac.checkpoint')
            ->getQuery()
            ->toIterable();
    }
}

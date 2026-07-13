<?php

namespace App\Repository;

use App\Domain\Common\Repository\RuntimeErrorRepository;
use App\Entity\RuntimeError;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

final class DoctrineRuntimeErrorRepository implements RuntimeErrorRepository
{
    /**
     * @var EntityRepository<RuntimeError>
     */
    private EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(RuntimeError::class);
    }

    public function create(RuntimeError $error): void
    {
        $this->entityManager->persist($error);
        $this->entityManager->flush();
    }

    public function paginated(int $start, int $perPage): iterable
    {
        return new Paginator(
            $this
                ->repository
                ->createQueryBuilder('r')
                ->orderBy('r.id', 'DESC')
                ->setFirstResult($start)
                ->setMaxResults($perPage)
        );
    }
}

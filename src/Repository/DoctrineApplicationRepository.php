<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Application\Exception\ApplicationDoesNotExist;
use App\Domain\Application\Repository\ApplicationRepository;
use App\Entity\Application;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;

final class DoctrineApplicationRepository implements ApplicationRepository
{
    /**
     * @var EntityRepository<Application>
     */
    private EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Application::class);
    }

    public function create(Application $application): Application
    {
        $this->entityManager->persist($application);
        $this->entityManager->flush();

        return $application;
    }

    public function all(): iterable
    {
        return $this->repository->findAll();
    }

    public function byId(ApplicationId $id): ?Application
    {
        return $this->repository->find($id->toString());
    }

    public function byIdStrict(ApplicationId $id): Application
    {
        return $this->byId($id) ?? throw ApplicationDoesNotExist::withId($id->toString());
    }

    public function update(Application $application): Application
    {
        return $this->create($application);
    }
}

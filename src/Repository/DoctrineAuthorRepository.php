<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Author\Repository\AuthorRepository;
use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class DoctrineAuthorRepository implements AuthorRepository
{
    /** @var EntityRepository<Author> */
    private EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Author::class);
    }

    public function findByName(string $name): ?Author
    {
        return $this->repository->findOneBy(['name' => $name]);
    }

    public function create(Author $author): Author
    {
        $this->entityManager->persist($author);
        $this->entityManager->flush();

        return $author;
    }

    public function all(): array
    {
        return array_values($this->repository->findBy([], ['name' => 'ASC']));
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Client\Repository\StreamRepository;
use App\Domain\Stream\Exception\StreamDoesNotExist;
use App\Entity\Stream;
use App\Entity\StreamEvent;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

final class DoctrineStreamRepository implements StreamRepository
{
    /**
     * @var EntityRepository<Stream>
     */
    private EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $this->entityManager->getRepository(Stream::class);
    }

    public function addEvent(Stream $stream, StreamEvent $event): void
    {
        $stream->addEvent($event);

        foreach ($event->getProperties() as $property) {
            $this->entityManager->persist($property);
        }

        $this->entityManager->persist($event);
    }

    public function update(Stream $stream): Stream
    {
        $this->entityManager->persist($stream);
        $this->entityManager->flush();

        return $stream;
    }

    public function paginated(int $start, int $end, ?string $search = null): \Countable&\IteratorAggregate
    {
        $queryBuilder = $this
            ->repository
            ->createQueryBuilder('s')
            ->select('s')
            ->setFirstResult($start)
            ->setMaxResults($end);

        if (null !== $search) {
            $queryBuilder
                ->where('LOWER(s.id) LIKE :search')
                ->setParameter('search', '%' . strtolower($search) . '%');
        }

        return new Paginator($queryBuilder->getQuery());
    }

    public function find(StreamId $id): ?Stream
    {
        return $this->repository->find($id->toString());
    }

    public function withId(StreamId $id): ?Stream
    {
        return $this->repository->find($id->toString());
    }

    public function withIdStrict(StreamId $id): Stream
    {
        return $this->repository->find($id->toString()) ?? throw StreamDoesNotExist::withId($id);
    }
}

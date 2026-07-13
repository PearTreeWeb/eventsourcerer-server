<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Event\Exception\NoEventFound;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventProperty as EventPropertyModel;
use App\Domain\Event\Model\EventPropertyId;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Model\EventVersion;
use App\Domain\Event\Repository\EventRepository;
use App\Entity\Event;
use App\Entity\EventProperty;
use App\Extension\Default\PropertyType\Json;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Clock\ClockInterface;

final class DoctrineEventRepository implements EventRepository
{
    /**
     * @var EntityRepository<Event>
     */
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClockInterface $clock
    ) {
        $this->repository = $entityManager->getRepository(Event::class);
    }

    public function create(Event $event): Event
    {
        $this->entityManager->persist($event);

        foreach ($event->getProperties() as $property) {
            $this->entityManager->persist($property);
        }

        $this->entityManager->flush();

        return $event;
    }

    public function update(Event $event): Event
    {
        return $this->create($event);
    }

    public function find(EventId $id): ?Event
    {
        if ($id->isAny()) {
            return $this->eventRepresentingAll();
        }

        return $this->repository->find($id->toString());
    }

    public function findByEventIds(array $ids): array
    {
        return $this->repository->findBy([
            'id' => self::mapIdsToStrings($ids),
        ]);
    }

    public function findByNameStrict(EventName $name, EventVersion $version): Event
    {
        if ($name->sameAs(EventName::any())) {
            return $this->eventRepresentingAll();
        }

        $event = $this->repository->findOneBy([
            'name'    => $name->toString(),
            'version' => $version->toInt(),
            'deleted' => false,
        ]);

        if (null === $event) {
            throw NoEventFound::withName($name);
        }

        return $event;
    }

    public function all(): array
    {
        return $this->repository->findAll();
    }

    public function allNonSystem(): array
    {
        return $this->repository->findBy([
            'systemEvent' => false,
        ]);
    }

    public function paginated(int $start, int $max, ?string $search = null): \Countable&\IteratorAggregate
    {
        $queryBuilder = $this
            ->repository
            ->createQueryBuilder('e')
            ->select('e')
            ->setFirstResult($start)
            ->setMaxResults($max);

        if (null !== $search) {
            $queryBuilder
                ->where('LOWER(e.name) LIKE :search')
                ->setParameter('search', '%' . strtolower($search) . '%');
        }

        return new Paginator($queryBuilder->getQuery());
    }

    public function eventRepresentingAll(): Event
    {
        $now = $this->clock->now();

        $event = Event::create(
            EventId::any(),
            'all',
            0,
            $now,
        );

        return $event->setProperties(
            new ArrayCollection([
                EventProperty::create(
                    new EventPropertyModel(
                        EventPropertyId::metadata(),
                        EventPropertyName::fromString('Metadata'),
                        Json::create(),
                        false,
                    ),
                    $now,
                )
            ])
        );
    }

    /**
     * @param EventId[] $ids
     *
     * @return string[]
     */
    private static function mapIdsToStrings(array $ids): array
    {
        return collect($ids)
            ->map(static fn (EventId $id): string => $id->toUuid()->toBinary())
            ->all();
    }

    public function findStrict(EventId $id): Event
    {
        return $this->find($id) ?? throw NoEventFound::withId($id);
    }

    public function allPersonalDataPropertyIds(): array
    {
        $allEvents = $this->all();
        $eventPropertiesWithPersonalData = [];
        foreach ($allEvents as $eventEntity) {
            $propertyIds = [];
            foreach ($eventEntity->getProperties() as $eventProperty) {
                if ($eventProperty->hasPersonalData()) {
                    $propertyIds[] = $eventProperty->id()->toRfc4122();
                }
            }
            if (!empty($propertyIds)) {
                $eventPropertiesWithPersonalData[$eventEntity->getId()->toString()] = $propertyIds;
            }
        }

        return $eventPropertiesWithPersonalData;
    }
}

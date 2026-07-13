<?php

declare(strict_types=1);

namespace App\Tests\Double\Repository;

use ApiPlatform\Doctrine\Orm\Extension\DoctrinePaginatorFactory;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventVersion;
use App\Domain\Event\Repository\EventRepository as EventRepositoryInterface;
use App\Entity\Event;
use App\Tests\Double\Entity;
use Doctrine\ORM\Tools\Pagination\Paginator;

final readonly class EventRepository implements EventRepositoryInterface
{
    public function create(Event $event): Event
    {
        return $event;
    }

    public function update(Event $event): Event
    {
        return $event;
    }

    public function find(EventId $id): Event
    {
        return Entity::event();
    }

    public function findByNameStrict(EventName $name, EventVersion $version): Event
    {
        return Entity::event();
    }

    public function findByEventIds(array $ids): array
    {
        return [Entity::event()];
    }

    public function all(): array
    {
        return $this->findByEventIds([]);
    }

    public function paginated(int $start, int $max, ?string $search = null): \Countable&\IteratorAggregate
    {
        throw new \RuntimeException('not implemented');
    }

    public function eventRepresentingAll(): Event
    {
        return Event::create(
            EventId::any(),
            EventName::any()->toString(),
            0,
            new \DateTimeImmutable()
        );
    }

    public function allNonSystem(): array
    {
        return [];
    }

    public function findStrict(EventId $id): Event
    {
        return $this->find($id);
    }
}

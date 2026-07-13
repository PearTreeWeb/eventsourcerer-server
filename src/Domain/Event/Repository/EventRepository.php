<?php

declare(strict_types=1);

namespace App\Domain\Event\Repository;

use App\Domain\Event\Exception\NoEventFound;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventVersion;
use App\Entity\Event;

interface EventRepository
{
    public const string ANY_EVENT_ID_REPRESENTATION = 'e89312a3-c5d1-47a8-a0aa-9a69455f845e';

    public function create(Event $event): Event;

    public function update(Event $event): Event;

    public function find(EventId $id): ?Event;

    /**
     * @throws NoEventFound
     */
    public function findStrict(EventId $id): Event;

    /**
     * @throws NoEventFound
     */
    public function findByNameStrict(EventName $name, EventVersion $version): Event;

    /**
     * @return Event[]
     */
    public function all(): array;

    /**
     * @return Event[]
     */
    public function allNonSystem(): array;

    /**
     * @param EventId[] $ids
     *
     * @return Event[]
     */
    public function findByEventIds(array $ids): array;

    /**
     * @return \Countable&\IteratorAggregate<int, Event>
     */
    public function paginated(int $start, int $max, ?string $search = null): \Countable&\IteratorAggregate;

    /**
     * @return array<string, string[]> map of eventId (string) to array of eventPropertyIds (string)
     */
    public function allPersonalDataPropertyIds(): array;

    public function eventRepresentingAll(): Event;
}

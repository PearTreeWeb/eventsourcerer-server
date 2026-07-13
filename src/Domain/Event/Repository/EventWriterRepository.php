<?php

namespace App\Domain\Event\Repository;

use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventVersion;

interface EventWriterRepository
{
    /**
     * @param EventId $eventId
     *
     * @return array<string, mixed>
     */
    public function eventPropertiesForEventWithId(EventId $eventId): array;

    /**
     * @return array{
     *      id: string,
     *      name: string,
     *      version: int,
     *      retired: bool,
     *      created_at: string,
     *      updated_at: string,
     *      system_event: bool,
     *      deleted: bool,
     *      tombstone_after_seconds:  int,
     * }|null
     */
    public function eventWithNameAndVersion(EventName $eventName, EventVersion $version): ?array;
}

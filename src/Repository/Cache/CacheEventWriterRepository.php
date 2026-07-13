<?php

declare(strict_types=1);

namespace App\Repository\Cache;

use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventVersion;
use App\Domain\Event\Repository\EventWriterRepository;
use App\Repository\Postgres\PostgresEventWriterRepository;
use Psr\Cache\CacheItemPoolInterface;

final readonly class CacheEventWriterRepository implements EventWriterRepository
{
    public function __construct(
        private PostgresEventWriterRepository $postgresEventRepository,
        private CacheItemPoolInterface $appEvent
    ) {}


    public function eventPropertiesForEventWithId(EventId $eventId): array
    {
        $cachedProperties = $this->appEvent->getItem($eventId->toString());
        $cacheValue = $cachedProperties->get();

        if (null !== $cacheValue) {
            return $cacheValue;
        }

        $eventProperties = $this->postgresEventRepository->eventPropertiesForEventWithId($eventId);

        $cachedProperties->set($eventProperties);
        $this->appEvent->save($cachedProperties);

        return $eventProperties;
    }

    public function eventWithNameAndVersion(EventName $eventName, EventVersion $version): ?array
    {
        $cacheKey = sprintf('%s-%d', $eventName, $version->toInt());

        $cacheEvent = $this->appEvent->getItem($cacheKey);
        $cacheValue = $cacheEvent->get();

        if (null !== $cacheValue) {
            return $cacheValue;
        }

        $event = $this->postgresEventRepository->eventWithNameAndVersion($eventName, $version );

        $cacheEvent->set($event);
        $this->appEvent->save($cacheEvent);

        return $event;
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final readonly class InFlightMessageStreamIds
{
    public function __construct(private CacheItemPoolInterface $appInflightMessages) {}

    public function addFor(ApplicationId $applicationId, StreamId $streamId): void
    {
        $cacheItem = $this->cache($applicationId);

        $inFlightStreamIds = $cacheItem->get() ?? [];

        $inFlightStreamIds[$streamId->toString()] = $streamId->toString();

        $cacheItem->set($inFlightStreamIds);

        $this->appInflightMessages->save($cacheItem);
    }

    public function removeFor(ApplicationId $applicationId, StreamId $streamId): void
    {
        $cacheItem = $this->cache($applicationId);

        $inFlightStreamIds = $cacheItem->get() ?? [];

        unset($inFlightStreamIds[$streamId->toString()]);

        $cacheItem->set($inFlightStreamIds);

        $this->appInflightMessages->save($cacheItem);
    }

    /**
     * @return string[]
     */
    public function for(ApplicationId $applicationId): array
    {
        return $this->cache($applicationId)->get() ?? [];
    }

    private function cache(ApplicationId $applicationId): CacheItemInterface
    {
        return $this->appInflightMessages->getItem($applicationId->toString());
    }
}

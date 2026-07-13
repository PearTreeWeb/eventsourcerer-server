<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Infrastructure\CatchupStatus;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;
use Psr\Cache\CacheItemPoolInterface;

final readonly class CatchupStatusProvider
{
    public function __construct(private CacheItemPoolInterface $appCatchupStatuses) {}

    public function setAsRunningFor(WorkerId $workerId): void
    {
        $this->setStatusFor($workerId, CatchupStatus::Running);
    }

    public function setAsPausedFor(WorkerId $workerId): void
    {
        $this->setStatusFor($workerId, CatchupStatus::Paused);
    }

    public function statusFor(WorkerId $workerId): CatchupStatus
    {
        return $this->appCatchupStatuses->getItem($workerId->toString())->get()
            ?? CatchupStatus::Stopped;
    }

    public function isPausedFor(WorkerId $workerId): bool
    {
        return $this->statusFor($workerId) === CatchupStatus::Paused;
    }

    private function setStatusFor(WorkerId $workerId, CatchupStatus $status): void
    {
        $statusCacheItem = $this->appCatchupStatuses->getItem($workerId->toString());

        $statusCacheItem->set($status);

        $this->appCatchupStatuses->save($statusCacheItem);
    }
}

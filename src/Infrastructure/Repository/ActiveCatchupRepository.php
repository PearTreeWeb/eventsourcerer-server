<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Application\Model\CatchupStatus;
use App\Domain\Application\Repository\ActiveCatchupRepository as ActiveCatchupRepositoryInterface;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use Psr\Cache\CacheItemPoolInterface;

final readonly class ActiveCatchupRepository implements ActiveCatchupRepositoryInterface
{
    public function __construct(private CacheItemPoolInterface $appActiveCatchups) {}

    public function addFor(ApplicationId $applicationId): void
    {
        $this->setStatusFor($applicationId, CatchupStatus::Active);
    }

    public function hasFor(ApplicationId $applicationId): bool
    {
        return $this->appActiveCatchups->hasItem($applicationId->toString());
    }

    public function removeFor(ApplicationId $applicationId): void
    {
        $this->appActiveCatchups->deleteItem($applicationId->toString());
    }

    public function setAsStaleFor(ApplicationId $applicationId): void
    {
        $this->setStatusFor($applicationId, CatchupStatus::Stale);
    }

    private function setStatusFor(ApplicationId $applicationId, CatchupStatus $status): void
    {
        $cacheItem = $this->appActiveCatchups->getItem($applicationId->toString());

        $cacheItem->set($status->value);

        $this->appActiveCatchups->save($cacheItem);
    }

    public function isStaleFor(ApplicationId $applicationId): bool
    {
        return CatchupStatus::Stale === $this->statusFor($applicationId);
    }

    private function statusFor(ApplicationId $applicationId): CatchupStatus
    {
        $cacheItem = $this->appActiveCatchups->getItem($applicationId->toString());
        $value = $cacheItem->get();

        if (null === $value) {
            return CatchupStatus::Inactive;
        }

        return CatchupStatus::from($value);
    }
}

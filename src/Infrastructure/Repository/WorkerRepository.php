<?php

namespace App\Infrastructure\Repository;

use App\Domain\Client\Model\ApplicationWorker;
use App\Infrastructure\Exception\NoWorkerFound;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;
use Psr\Cache\CacheItemPoolInterface;

final readonly class WorkerRepository
{
    private const string APPLICATION_WORKERS = 'applicationWorkers';
    private const string LAST_FORWARDED_CHECKPOINT_PREFIX = 'lastForwardedCheckpoint';

    public function __construct(private CacheItemPoolInterface $workerCache) {}

    public function add(ApplicationWorker $applicationWorker): void
    {
        $this->cacheIndividualWorker($applicationWorker);
        $this->cacheApplicationWorker($applicationWorker);
    }

    public function remove(WorkerId $workerId): void
    {
        /** @var null|ApplicationWorker $worker */
        $worker = $this->workerCache->getItem($workerId->toString())->get();

        if (null === $worker) {
            return;
        }

        $this->workerCache->deleteItem($workerId->toString());
        $this->removeApplicationWorker($worker);
    }

    public function find(WorkerId $workerId): ?ApplicationWorker
    {
        return $this->workerCache->getItem($workerId->toString())->get();
    }

    public function findStrict(WorkerId $workerId): ApplicationWorker
    {
        return $this->workerCache->getItem($workerId->toString())->get()
            ?? throw NoWorkerFound::withId($workerId);
    }

    public function oneWorkerForApplication(ApplicationId $applicationId): ?WorkerId
    {
        $applicationWorkers = $this->workerCache->getItem(self::APPLICATION_WORKERS);
        $current = $applicationWorkers->get() ?? [];
        $workers = $current[$applicationId->toString()] ?? [];

        if (empty($workers)) {
            return null;
        }

        return $workers[array_rand($workers)];
    }

    public function lastForwardedCheckpoint(WorkerId $workerId, StreamId $streamId): ?int
    {
        $item = $this->workerCache->getItem($this->lastCheckpointKey($workerId, $streamId));
        $value = $item->get();

        return is_int($value) ? $value : null;
    }

    public function setLastForwardedCheckpoint(WorkerId $workerId, StreamId $streamId, int $checkpoint): void
    {
        $item = $this->workerCache->getItem($this->lastCheckpointKey($workerId, $streamId));
        $item->set($checkpoint);
        $this->workerCache->save($item);
    }

    /**
     * @return iterable<mixed>
     */
    public function all(): iterable
    {
        return $this->workerCache->getItem(self::APPLICATION_WORKERS)->get() ?? [];
    }

    private function lastCheckpointKey(WorkerId $workerId, StreamId $streamId): string
    {
        return sprintf(
            '%s.%s.%s',
            self::LAST_FORWARDED_CHECKPOINT_PREFIX,
            $workerId->toString(),
            $streamId->toString()
        );
    }

    private function cacheIndividualWorker(ApplicationWorker $applicationWorker): void
    {
        $workerCacheItem = $this->workerCache->getItem($applicationWorker->workerId->toString());

        $workerCacheItem->set($applicationWorker);

        $this->workerCache->save($workerCacheItem);
    }

    private function cacheApplicationWorker(ApplicationWorker $applicationWorker): void
    {
        $applicationWorkers = $this->workerCache->getItem(self::APPLICATION_WORKERS);

        $current = $applicationWorkers->get() ?? [];

        $current[$applicationWorker->applicationId->toString()][$applicationWorker->workerId->toString()]
            = $applicationWorker->workerId;

        $applicationWorkers->set($current);

        $this->workerCache->save($applicationWorkers);
    }

    private function removeApplicationWorker(ApplicationWorker $worker): void
    {
        $applicationWorkers = $this->workerCache->getItem(self::APPLICATION_WORKERS);

        $current = $applicationWorkers->get();

        unset($current[$worker->applicationId->toString()][$worker->workerId->toString()]);

        $applicationWorkers->set($current);

        $this->workerCache->save($applicationWorkers);
    }
}

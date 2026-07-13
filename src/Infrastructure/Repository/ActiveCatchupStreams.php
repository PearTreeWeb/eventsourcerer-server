<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class ActiveCatchupStreams
{
    public function __construct(private CacheItemPoolInterface $activeCatchupStreams) {}

    private static function itemKey(ApplicationId $applicationId, StreamId $streamId): string
    {
        return str_replace('-', '_', $applicationId->toString() . '_' . $streamId->toString());
    }

    private static function indexKey(ApplicationId $applicationId): string
    {
        return 'index_' . str_replace('-', '_', $applicationId->toString());
    }

    public function add(StreamId $streamId, ApplicationId $applicationId): void
    {
        $key = self::itemKey($applicationId, $streamId);
        $item = $this->activeCatchupStreams->getItem($key);
        $item->set($streamId->toString());
        $this->activeCatchupStreams->save($item);

        $indexKey = self::indexKey($applicationId);
        $indexItem = $this->activeCatchupStreams->getItem($indexKey);
        $index = $indexItem->get() ?? [];
        $index[$streamId->toString()] = true;
        $indexItem->set($index);
        $this->activeCatchupStreams->save($indexItem);
    }

    public function remove(StreamId $streamId, ApplicationId $applicationId): void
    {
        $key = self::itemKey($applicationId, $streamId);
        $this->activeCatchupStreams->deleteItem($key);

        $indexKey = self::indexKey($applicationId);
        $indexItem = $this->activeCatchupStreams->getItem($indexKey);
        $index = $indexItem->get() ?? [];
        unset($index[$streamId->toString()]);
        $indexItem->set($index);
        $this->activeCatchupStreams->save($indexItem);
    }

    public function removeAllForApplication(ApplicationId $applicationId): void
    {
        $indexKey = self::indexKey($applicationId);
        $indexItem = $this->activeCatchupStreams->getItem($indexKey);
        $index = $indexItem->get() ?? [];

        foreach (array_keys($index) as $streamIdString) {
            $this->activeCatchupStreams->deleteItem(self::itemKey($applicationId, StreamId::fromString($streamIdString)));
        }

        $indexItem->set([]);
        $this->activeCatchupStreams->save($indexItem);
    }

    public function printFor(ApplicationId $applicationId, OutputInterface $output): void
    {
        foreach ($this->allForApplication($applicationId) as $activeCatchupStream) {
            $output->writeln(
                sprintf(
                    'Stream %s is being read',
                    $activeCatchupStream->toString()
                )
            );
        }
    }

    /**
     * @return iterable<StreamId>
     */
    public function allForApplication(ApplicationId $applicationId): iterable
    {
        try {
            $indexItem = $this->activeCatchupStreams->getItem(self::indexKey($applicationId));
        } catch (InvalidArgumentException) {
            return [];
        }

        $index = $indexItem->get() ?? [];

        foreach (array_keys($index) as $streamIdString) {
            yield StreamId::fromString($streamIdString);
        }
    }

    public function summary(ApplicationId $applicationId): string
    {
        $summary = '';

        foreach ($this->allForApplication($applicationId) as $activeStream) {
            $summary .= $activeStream . ', ';
        }

        return rtrim($summary, ', ');
    }

    /**
     * @param ApplicationId[] $applicationIds
     *
     * @return iterable<mixed>
     */
    public function all(array $applicationIds): iterable
    {
        foreach ($applicationIds as $applicationId) {
            yield from $this->allForApplication($applicationId);
        }
    }
}

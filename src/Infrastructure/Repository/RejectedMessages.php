<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Infrastructure\Message\Rejection;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Psr\Cache\CacheItemPoolInterface;

final readonly class RejectedMessages
{
    public function __construct(private CacheItemPoolInterface $appRejectedMessages) {}

    public function add(Rejection ...$messages): void
    {
        foreach ($messages as $message) {
            $cacheItem = $this->appRejectedMessages->getItem($message->streamId()->toString());

            $cacheItem->set(self::allMessages($message, $cacheItem->get()));

            $this->appRejectedMessages->save($cacheItem);
        }
    }

    /**
     * @return iterable<Rejection>
     */
    public function find(StreamId $streamId): iterable
    {
        $cacheItem = $this->appRejectedMessages->getItem($streamId->toString());

        return $cacheItem->isHit()
            ? \array_values($cacheItem->get())
            : [];
    }

    public function remove(Rejection $message): void
    {
        $messages = collect($this->find($message->streamId()))
            ->reject(
                static fn (Rejection $collectionMessage) => $collectionMessage
                    ->checkpoint()
                    ->isSameAs($message->checkpoint())
        );

        $cacheItem = $this->appRejectedMessages->getItem($message->streamId()->toString());

        $cacheItem->set($messages->values()->all());

        $this->appRejectedMessages->save($cacheItem);
    }

    /**
     * @param iterable<Rejection>|null $currentMessages
     *
     * @return iterable<Rejection>
     */
    private static function allMessages(Rejection $message, ?iterable $currentMessages = []): iterable
    {
        $currentMessages[$message->checkpoint()->toInt()] = $message;

        return $currentMessages;
    }
}

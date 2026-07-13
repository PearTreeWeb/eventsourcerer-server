<?php

declare(strict_types=1);

namespace App\Controller\SimpleServerEvent;

use App\Domain\Stream\Query\GetStreamEventsAfterSequence;
use App\Infrastructure\QueryBus;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\EventStreamResponse;
use Symfony\Component\HttpFoundation\ServerEvent;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/simple-server-events/{id}/new-events/{start}',
    name: 'simple_server_events_for_new_events',
)]
final class NewEvents extends AbstractController
{
    private const string NEW_EVENT_MAX_SEQUENCE_RECEIVED = 'maxSequenceReceived_%s';

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CacheItemPoolInterface $appSseNewEvents,
    ) {}

    public function __invoke(StreamId $id, string $start): EventStreamResponse
    {
        $listenToStream = StreamId::fromString($id->toString());

        if ('all' === $id->toString()) {
            $listenToStream = StreamId::allStream();
        }

        $cacheKey  = sprintf(self::NEW_EVENT_MAX_SEQUENCE_RECEIVED, $listenToStream->toString());
        $cacheItem = $this->appSseNewEvents->getItem($cacheKey);
        $startInt  = (int) $start;

        if (!$cacheItem->isHit() || $startInt > (int) $cacheItem->get()) {
            $cacheItem->set($startInt);

            $this->appSseNewEvents->save($cacheItem);
        }

        return new EventStreamResponse(function () use ($listenToStream, $cacheKey) {
            $newEvents = $this->queryBus->query(
                GetStreamEventsAfterSequence::withStreamId(
                    $listenToStream,
                    $this->appSseNewEvents->getItem($cacheKey)->get(),
                    10,
                    true,
                )
            );

            $cacheItem = $this->appSseNewEvents->getItem($cacheKey);

            foreach ($newEvents as $event) {
                $cacheItem->set($listenToStream->isAllStream() ? $event->getAllSequence() : $event->getSequence());
                $this->appSseNewEvents->save($cacheItem);

                yield new ServerEvent(
                    json_encode([
                        'sequence' => $event->getSequence(),
                        'name' => $event->getName(),
                        'occurred' => $event->createdAt()->format('jS M Y H:i:s'),
                    ]),
                    type: 'newEvent',
                );
            }

            sleep(5);
        });
    }
}

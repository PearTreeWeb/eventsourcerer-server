<?php

declare(strict_types=1);

namespace App\Domain\Event\Handler;

use App\Domain\Event\Query\GetEventsWithIds;
use App\Domain\Event\Repository\EventRepository;
use App\Entity\Event;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetEventsWithIdsHandler
{
    public function __construct(private EventRepository $eventRepository) {}

    /**
     * @return Event[]
     */
    public function __invoke(GetEventsWithIds $query): array
    {
        return $this->eventRepository->findByEventIds($query->ids);
    }
}

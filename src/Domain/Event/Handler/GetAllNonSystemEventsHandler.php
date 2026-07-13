<?php

namespace App\Domain\Event\Handler;

use App\Domain\Event\Query\GetAllNonSystemEvents;
use App\Domain\Event\Repository\EventRepository;
use App\Entity\Event;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetAllNonSystemEventsHandler
{
    public function __construct(private EventRepository $eventRepository) {}

    /**
     * @return Event[]
     */
    public function __invoke(GetAllNonSystemEvents $query): array
    {
        return $this->eventRepository->allNonSystem();
    }
}
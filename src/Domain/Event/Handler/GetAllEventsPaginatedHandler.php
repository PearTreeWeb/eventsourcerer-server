<?php

declare(strict_types=1);

namespace App\Domain\Event\Handler;

use App\Domain\Event\Query\GetAllEventsPaginated;
use App\Domain\Event\Repository\EventRepository;
use App\Entity\Event;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetAllEventsPaginatedHandler
{
    public function __construct(private EventRepository $eventRepository) {}

    /**
     * @return iterable<Event>
     */
    public function __invoke(GetAllEventsPaginated $query): iterable
    {
        return $this->eventRepository->paginated($query->start, $query->max, $query->search);
    }
}

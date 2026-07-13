<?php

declare(strict_types=1);

namespace App\Domain\Event\Handler;

use App\Domain\Event\Query\GetEventByName;
use App\Domain\Event\Repository\EventRepository;
use App\Entity\Event;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetEventByNameHandler
{
    public function __construct(private EventRepository $eventRepository) {}

    public function __invoke(GetEventByName $query): Event
    {
        return $this->eventRepository->findByNameStrict($query->name, $query->version);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Event\Handler;

use App\Domain\Event\Query\GetEventRepresentingAll;
use App\Domain\Event\Repository\EventRepository;
use App\Entity\Event;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetEventRepresentingAllHandler
{
    public function __construct(private EventRepository $eventRepository) {}

    public function __invoke(GetEventRepresentingAll $query): Event
    {
        return $this->eventRepository->eventRepresentingAll();
    }
}

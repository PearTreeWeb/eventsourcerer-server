<?php

declare(strict_types=1);

namespace App\Domain\Event\Handler;

use App\Domain\Event\Query\GetEvent;
use App\Domain\Event\Repository\EventRepository;
use App\Entity\Event;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetEventHandler
{
    public function __construct(private EventRepository $eventRepository) {}

    public function __invoke(GetEvent $query): Event
    {
        return $this->eventRepository->find($query->id);
    }
}

<?php

namespace App\Controller\Event;

use App\Domain\Stream\Query\GetStreamEvents;
use App\Infrastructure\QueryBus;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\EventStreamResponse;
use Symfony\Component\HttpFoundation\ServerEvent;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/event/{id}/listener/{afterCheckpoint}', name: 'event_listener')]
#[IsGranted('ROLE_USER')]
final class Listener extends AbstractController
{
    public function __construct(private readonly QueryBus $queryBus) {}

    public function __invoke(StreamId $id, int $afterCheckpoint): EventStreamResponse
    {
        return new EventStreamResponse(function () use ($id, $afterCheckpoint) {
            foreach ($this->queryBus->query(GetStreamEvents::withStreamId($id, $afterCheckpoint, 1)) as $streamEvent) {
                yield new ServerEvent($streamEvent->toScalarArray(), type: 'jobs');

                sleep(5);
            }
        });
    }
}

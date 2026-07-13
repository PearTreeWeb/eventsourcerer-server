<?php

declare(strict_types=1);

namespace App\Controller\Incoming;

use App\Infrastructure\EventSourcerer\Service\AcknowledgeEvent;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\IsString;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stream_events/{id}/ack', name: 'ack_stream_event', methods: ['POST'])]
final class AckStreamEvent extends AbstractController
{
    public function __construct(
        private readonly AcknowledgeEvent $acknowledgeEvent
    ) {}

    public function __invoke(StreamId $id, Request $request): Response
    {
        $this->acknowledgeEvent->acknowledge(
            Checkpoint::fromString($request->getPayload()->get('checkpoint')),
            Checkpoint::fromString($request->getPayload()->get('allStreamCheckpoint')),
            ApplicationId::fromString($request->getPayload()->get('applicationId')),
            $id,
            WorkerId::fromString(IsString::NULL_REPRESENTATION)
        );

        return new Response('Message acknowledged');
    }
}

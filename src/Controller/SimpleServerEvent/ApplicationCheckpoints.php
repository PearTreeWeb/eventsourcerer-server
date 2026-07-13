<?php

declare(strict_types=1);

namespace App\Controller\SimpleServerEvent;

use App\Domain\Application\Query\GetApplicationCheckpointsWithMaxSequences;
use App\Infrastructure\QueryBus;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\EventStreamResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ServerEvent;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/simple-server-events/application/{applicationId}/checkpoints',
    name: 'simple_server_events_for_application_checkpoints',
)]
final class ApplicationCheckpoints extends AbstractController
{
    public function __construct(private readonly QueryBus $queryBus) {}

    public function __invoke(ApplicationId $applicationId): Response
    {
        return new EventStreamResponse(function () use ($applicationId) {
            /** @var iterable<array{streamId: string, checkpoint: int, maxSequence: int}> $checkpoints */
            $checkpoints = $this->queryBus->query(new GetApplicationCheckpointsWithMaxSequences($applicationId));

            foreach ($checkpoints as $checkpoint) {
                yield new ServerEvent(
                    json_encode([
                        'stream' => $checkpoint['streamId'],
                        'checkpoint' => $checkpoint['checkpoint'],
                        'maxSequence' => $checkpoint['maxSequence'],
                    ], JSON_THROW_ON_ERROR)
                    , type: 'acknowledgement'
                );
            }

            sleep(1);
        });
    }
}

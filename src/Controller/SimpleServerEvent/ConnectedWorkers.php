<?php

namespace App\Controller\SimpleServerEvent;

use App\Domain\Application\Query\GetApplication;
use App\Infrastructure\QueryBus;
use App\Infrastructure\Repository\WorkerRepository;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\EventStreamResponse;
use Symfony\Component\HttpFoundation\ServerEvent;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/simple-server-events/connected-workers',
    name: 'simple_server_events_connected_workers',
)]
final class ConnectedWorkers extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly WorkerRepository $workerRepository,
    ) {}

    public function __invoke(): EventStreamResponse
    {
        return new EventStreamResponse(function () {
            yield new ServerEvent(
                json_encode(\iterator_to_array($this->activeWorkers())),
                type: 'activeWorkersUpdated',
            );

            sleep(2);
        });
    }

    /**
     * @return iterable<string, string[]>
     */
    private function activeWorkers(): iterable
    {
        foreach ($this->workerRepository->all() as $applicationId => $applicationWorkers) {
            $application = $this->queryBus->query(
                new GetApplication(ApplicationId::fromString($applicationId))
            );

            yield $application->name() => array_map(
                fn (WorkerId $workerId) => $workerId->toString(),
                $applicationWorkers
            );
        }
    }
}

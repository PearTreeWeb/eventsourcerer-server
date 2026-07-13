<?php

namespace App\Controller\Monitoring;

use App\Domain\Application\Query\GetApplication;
use App\Infrastructure\QueryBus;
use App\Infrastructure\Repository\WorkerRepository;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/monitoring/workers', name: 'monitoring_workers', methods: ['GET'])]
final class ActiveWorkers extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly WorkerRepository $workerRepository,
    ) {}

    public function __invoke(): Response
    {
        return $this->render('UI/monitoring/active_workers.html.twig', [
            'activeWorkers' => $this->activeWorkers(),
        ]);
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

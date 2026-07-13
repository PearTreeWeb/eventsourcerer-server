<?php

namespace App\Controller\Monitoring;

use App\Domain\Application\Query\GetAllApplications;
use App\Entity\Application;
use App\Infrastructure\QueryBus;
use App\Infrastructure\Repository\ActiveCatchupStreams as ActiveCatchupStreamsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/monitoring/active-catchup-streams', name: 'active_catchup_streams', methods: ['GET'])]
final class ActiveCatchupStreams extends AbstractController
{
    public function __construct(
        private readonly ActiveCatchupStreamsRepository $activeCatchupStreams,
        private readonly QueryBus $queryBus,
    ) {}

    public function __invoke(): Response
    {
        $applications = $this->queryBus->query(new GetAllApplications());

        $applicationIds = array_map(
            fn (Application $application) => $application->applicationId(),
            \iterator_to_array($applications),
        );

        return $this->render('UI/monitoring/active_catchup_streams.html.twig', [
            'activeCatchupStreams' => $this->activeCatchupStreams->all($applicationIds),
        ]);
    }
}
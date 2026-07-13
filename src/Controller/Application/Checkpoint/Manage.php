<?php

declare(strict_types=1);

namespace App\Controller\Application\Checkpoint;

use App\Domain\Application\Query\GetApplicationCheckpoints;
use App\Infrastructure\SymfonyQueryBus;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/application/{id}/checkpoints/manage', name: 'manage_application_checkpoints')]
final class Manage extends AbstractController
{
    public function __construct(private readonly SymfonyQueryBus $queryBus) {}

    public function __invoke(ApplicationId $id): Response
    {
        $checkpoints = $this->queryBus->query(
            new GetApplicationCheckpoints($id)
        );

        return $this->render(
            'UI/application/manage_checkpoints.html.twig',
            [
                'applicationId' => $id->toString(),
                'checkpoints'   => $checkpoints,
            ]
        );
    }
}

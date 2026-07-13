<?php

namespace App\Controller\Projection;

use App\Domain\Projection\Command\ReviveProjection;
use App\Domain\Projection\Model\ProjectionId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/projection/{id}/revive', name: 'revive_projection')]
#[IsGranted('ROLE_SUPER_USER')]
final class Revive extends AbstractController
{
    public function __construct(private readonly MessageBusInterface $commandBus) {}

    public function __invoke(ProjectionId $id): Response
    {
        $this->commandBus->dispatch(new ReviveProjection($id));

        return $this->redirectToRoute('edit_projection', ['id' => $id->toString()]);
    }
}
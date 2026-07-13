<?php

declare(strict_types=1);

namespace App\Controller\Projection;

use App\Domain\Projection\Command\DeleteProjection;
use App\Domain\Projection\Model\ProjectionId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/projection/{id}/delete', name: 'delete_projection', methods: ['DELETE'])]
#[IsGranted('ROLE_USER')]
final class Delete extends AbstractController
{
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'projection_deleted_successfully';

    public function __construct(private readonly MessageBusInterface $commandBus) {}

    public function __invoke(ProjectionId $id): Response
    {
        $this->commandBus->dispatch(new DeleteProjection($id));

        $this->addFlash('success', self::SUCCESS_MSG_TRANSLATION_KEY);

        return $this->redirectToRoute('projections');
    }
}

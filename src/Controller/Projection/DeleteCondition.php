<?php

declare(strict_types=1);

namespace App\Controller\Projection;

use App\Domain\Projection\Command\DeleteCondition as DeleteConditionCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/dashboard/projection/{projectionId}/condition/{id}',
    name: 'delete_projection_condition',
    methods: ['DELETE']
)]
final class DeleteCondition extends AbstractController
{
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'mutation_condition_deleted_successfully';
    private const string ERROR_MSG_TRANSLATION_KEY = 'mutation_condition_deletion_failed';

    public function __construct(private readonly MessageBusInterface $commandBus) {}

    public function __invoke(int $id): Response
    {
        try {
            $this->commandBus->dispatch(new DeleteConditionCommand($id));
            $this->addFlash('success', self::SUCCESS_MSG_TRANSLATION_KEY);
        } catch (\Throwable) {
           $this->addFlash('warning', self::ERROR_MSG_TRANSLATION_KEY);
        }

        return $this->redirectToRoute('projections');
    }
}

<?php

namespace App\Controller\Projection;

use App\Domain\Projection\Command\DeleteConditionsGroup as DeleteConditionsGroupCommand;
use App\Domain\Projection\Model\ProjectionMutationId;
use App\Domain\Projection\Model\ProjectionPropertyId;
use App\Domain\Projection\Query\GetProjectionMutation;
use App\Infrastructure\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/projection/{projectionMutationId}/conditions/group/{id}/delete', name: 'delete_projection_conditions_group', methods: ['DELETE'])]
#[IsGranted('ROLE_USER')]
final class DeleteConditionsGroup extends AbstractController
{
    private const string ERROR_MSG_TRANSLATION_KEY = 'conditions_group_deletion_failed';
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'conditions_group_deleted_successfully';

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly MessageBusInterface $commandBus
    ) {}

    public function __invoke(
        ProjectionMutationId $projectionMutationId,
        int $id,
        Request $request
    ): Response {
        try {
            $projectionMutation = $this->queryBus->query(new GetProjectionMutation($projectionMutationId));
            $projectionId = $projectionMutation->getProjectionId();

            $this->commandBus->dispatch(
                new DeleteConditionsGroupCommand(
                    $id,
                    $projectionId,
                    ProjectionPropertyId::fromUuid($projectionMutation->getProjectionProperty()->id()),
                    $projectionMutationId
                )
            );

            $this->addFlash('success', self::SUCCESS_MSG_TRANSLATION_KEY);
        } catch (\Throwable) {
            $this->addFlash('warning', self::ERROR_MSG_TRANSLATION_KEY);

            return $this->redirectToRoute('projections');
        }

        return $this->redirectToRoute(
            'define_projection_mutations',
            [
                'id' => $projectionId->toString(),
                'projectionEventPropertyId' => $projectionMutation->getProjectionEventPropertyId()->toString(),
            ]
        );
    }
}

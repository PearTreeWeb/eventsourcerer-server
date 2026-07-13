<?php

declare(strict_types=1);

namespace App\Controller\Projection;

use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Query\GetProjection;
use App\Domain\Projection\Query\GetProjectionMasterStateById;
use App\Domain\Projection\Service\RunProjectionMutation;
use App\Domain\User\Model\UserId;
use App\Entity\User;
use App\Form\Projection\EditProjectionType;
use App\Infrastructure\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/projection/{id}/edit', name: 'edit_projection')]
#[IsGranted('ROLE_OBSERVER')]
final class Edit extends AbstractController
{
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'state_edited_successfully';

    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly QueryBus $queryBus,
        private readonly Security $security,
    ) {}

    public function __invoke(ProjectionId $id, #[CurrentUser] User $user, Request $request): Response
    {
        $projection = $this->queryBus->query(GetProjection::withId($id));
        $projectionMasterState = $this->queryBus->query(new GetProjectionMasterStateById($id));

        $form = null;

        if ($this->security->isGranted('ROLE_SUPER_USER')) {
            $form = $this->createForm(
                EditProjectionType::class,
                [
                    'partition' => $projection->isPartitioned(),
                    'projectionName' => $projection->getName(),
                    'properties' => $projection->eventProperties()->toFormArray(),
                    'projectionId' => $projection->getId(),
                    'continuous' => $projection->isContinuous(),
                    'userId' => UserId::fromUuid($user->getId()),
                    'exposeStateViaApi' => $projection->getExposeStateViaApi(),
                    'startDate' => $projection->getStartDate(),
                    'endDate' => $projection->getEndDate(),
                ],
            );

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $command = $form->getData();

                $this->commandBus->dispatch($command);
                $this->addFlash('success', self::SUCCESS_MSG_TRANSLATION_KEY);

                return $this->redirectToRoute('edit_projection', ['id' => $id]);
            }
        }

        $partitionedResults = $projection->getCurrentState()[RunProjectionMutation::PARTITIONED_RESULTS] ?? [];

        return $this->render(
            'UI/projection/edit.html.twig',
             [
                 'form' => $form,
                 'partitionedResults' => json_encode($partitionedResults, JSON_THROW_ON_ERROR),
                 'projection' => $projection,
                 'currentState' => $projectionMasterState?->getCurrentState(),
             ]
        );
    }
}

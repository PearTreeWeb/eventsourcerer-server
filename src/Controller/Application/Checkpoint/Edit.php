<?php

declare(strict_types=1);

namespace App\Controller\Application\Checkpoint;

use App\Domain\Application\Command\OverrideCheckpoint;
use App\Domain\Application\Query\GetApplication;
use App\Domain\Application\Query\GetApplicationCheckpoint;
use App\Entity\ApplicationCheckpoint;
use App\Form\Application\EditApplicationStreamCheckpointType;
use App\Infrastructure\QueryBus;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    '/dashboard/application/{applicationId}/checkpoint/{streamId}/edit',
    name: 'edit_application_checkpoint_for_stream',
    requirements: ['applicationId' => Requirement::UUID],
    methods: [Request::METHOD_GET, Request::METHOD_POST]
)]
#[IsGranted('ROLE_SUPER_USER')]
final class Edit extends AbstractController
{
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'application_stream_checkpoint_updated_successfully';

    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly MessageBusInterface $commandBus
    ) {}

    public function __invoke(
        ApplicationId $applicationId,
        StreamId $streamId,
        Request $request
    ): Response {
        $application = $this->queryBus->query(new GetApplication($applicationId));

        /** @var ApplicationCheckpoint $checkpoint */
        $checkpoint = $this->queryBus->query(new GetApplicationCheckpoint($applicationId, $streamId));

        $form = $this->createForm(EditApplicationStreamCheckpointType::class, [
            'applicationId' => $applicationId,
            'checkpoint'    => Checkpoint::fromInt($checkpoint->getCheckpoint()),
            'streamId'      => $streamId,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $checkpoint = $form->getData()['checkpoint'];

                $this->commandBus->dispatch(OverrideCheckpoint::for($applicationId, $streamId, $checkpoint));

                $this->addFlash('success', self::SUCCESS_MSG_TRANSLATION_KEY);

                return $this->redirectToRoute('manage_application_checkpoints', ['id' => $applicationId]);
            } catch (\Throwable $e) {
                // @todo handle this!
            }
        }

        return $this->render('UI/application/edit_checkpoint.html.twig', [
            'application' => $application,
            'form'        => $form,
        ]);
    }
}

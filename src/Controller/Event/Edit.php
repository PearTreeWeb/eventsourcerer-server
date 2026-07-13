<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Domain\Event\Command\EditEvent;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Query\GetEvent;
use App\Form\Event\EditEventType;
use App\Infrastructure\SymfonyQueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/event/{id}/edit', name: 'edit_event')]
#[IsGranted('ROLE_SUPER_USER')]
final class Edit extends AbstractController
{
    private const string EXCLUDED_FROM_PROJECTIONS_MSG_TRANSLATION_KEY = 'event_excluded_from_projections';
    private const string SUCCESS_MSG_TRANSLATION_KEY                   = 'event_updated_successfully';

    public function __construct(
        private readonly SymfonyQueryBus $queryBus,
        private readonly MessageBusInterface $commandBus
    ) {}

    public function __invoke(EventId $id, Request $request): Response
    {
        $event = $this->queryBus->query(GetEvent::withId($id));
        $form = $this->createForm(EditEventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var EditEvent $command */
                $command = $form->getData();

                $this->commandBus->dispatch($command);

                $this->addFlash('success', self::SUCCESS_MSG_TRANSLATION_KEY);
                $this->addFlash('warning', self::EXCLUDED_FROM_PROJECTIONS_MSG_TRANSLATION_KEY);

                return $this->redirectToRoute('edit_event', ['id' => $command->id]);
            } catch (\Throwable $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render(
            'UI/event/edit.html.twig',
            [
                'form' => $form,
                'event' => $event,
            ]
        );
    }
}

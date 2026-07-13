<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Domain\Event\Command\RegisterEvent;
use App\Form\Event\RegisterEventType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/event/register', name: 'register_event')]
#[IsGranted('ROLE_SUPER_USER')]
final class Register extends AbstractController
{
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'event_registered_successfully';

    public function __construct(private readonly MessageBusInterface $commandBus) {}

    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(RegisterEventType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var RegisterEvent $command */
                $command = $form->getData();

                $this->commandBus->dispatch($command);

                $this->addFlash('success', self::SUCCESS_MSG_TRANSLATION_KEY);

                return $this->redirectToRoute('edit_event', ['id' => $command->id]);
            } catch (\Throwable $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render(
            'UI/event/register.html.twig',
            [
                'form' => $form,
            ]
        );
    }
}

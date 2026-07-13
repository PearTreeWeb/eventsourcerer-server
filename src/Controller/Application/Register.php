<?php

declare(strict_types=1);

namespace App\Controller\Application;

use App\Form\Application\RegisterApplicationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/application/register', name: 'register_application')]
#[IsGranted('ROLE_SUPER_USER')]
final class Register extends AbstractController
{
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'application_registered_successfully';

    public function __construct(private readonly MessageBusInterface $commandBus) {}

    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(RegisterApplicationType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $command = $form->getData();

                $envelope = $this->commandBus->dispatch($command);
                $secret = $envelope->last(HandledStamp::class)?->getResult();

                $this->addFlash('success', self::SUCCESS_MSG_TRANSLATION_KEY);
                $this->addFlash('info', 'Your application secret is: ' . $secret . '. Please save it as it will not be shown again.');

                return $this->redirectToRoute('edit_application', ['id' => $command->id]);
            } catch (\Throwable $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render(
            'UI/application/register.html.twig',
            [
                'form' => $form,
            ]
        );
    }
}

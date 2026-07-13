<?php

declare(strict_types=1);

namespace App\Controller\Projection;

use App\Domain\User\Model\UserId;
use App\Entity\User;
use App\Form\Projection\RegisterProjectionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/projection/register', name: 'register_projection')]
#[IsGranted('ROLE_SUPER_USER')]
final class Register extends AbstractController
{
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'projection_registered_successfully';

    public function __construct(private readonly MessageBusInterface $commandBus) {}

    public function __invoke(Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->createForm(
            RegisterProjectionType::class,
            [
                'userId' => UserId::fromUuid($user->getId()),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = $form->getData();

            $this->commandBus->dispatch($command);

            $this->addFlash('success', self::SUCCESS_MSG_TRANSLATION_KEY);

            return $this->redirectToRoute('edit_projection', ['id' => $command->id]);
        }

        return $this->render('UI/projection/register.html.twig', [
            'form' => $form,
        ]);
    }
}

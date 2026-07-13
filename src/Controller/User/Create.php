<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Domain\User\Command\RegisterUser;
use App\Form\User\AddUserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user/create', name: 'create-user')]
#[IsGranted('ROLE_SUPER_USER')]
final class Create extends AbstractController
{
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'user_registered_successfully';

    public function __construct(private readonly MessageBusInterface $commandBus) {}

    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(AddUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new RegisterUser($form->getData()));

            $this->addFlash(
                'success',
                self::SUCCESS_MSG_TRANSLATION_KEY
            );

            return $this->redirectToRoute('users');
        }

        return $this->render('UI/user/create.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}

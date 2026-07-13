<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Domain\User\Command\RegisterUser;
use App\Entity\User;
use App\Form\User\FirstRegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_SUPER_USER')]
final class Register extends AbstractController
{
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'user_registered_successfully';

    public function __construct(private readonly MessageBusInterface $commandBus) {}

    public function __invoke(Request $request): Response {
        $form = $this->createForm(FirstRegistrationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $user->setAsSuperUser();

            $this->commandBus->dispatch(new RegisterUser($user));

            $this->addFlash(
                'success',
                self::SUCCESS_MSG_TRANSLATION_KEY
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('UI/user/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}

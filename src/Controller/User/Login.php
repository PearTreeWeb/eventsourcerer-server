<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Domain\User\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/login', name: 'app_login')]
final class Login extends AbstractController
{
    public function __invoke(AuthenticationUtils $authenticationUtils, UserRepository $userRepository): Response
    {
        if (count($userRepository->all()) === 0) {
            return $this->redirectToRoute('setup');
        }

        $error        = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('UI/user/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }
}

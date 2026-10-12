<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Setup\InstallStateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/login', name: 'app_login')]
final class Login extends AbstractController
{
    public function __invoke(AuthenticationUtils $authenticationUtils, InstallStateService $installState): Response
    {
        if (!$installState->isInstalled()) {
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

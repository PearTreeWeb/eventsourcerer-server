<?php

declare(strict_types=1);

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/logout', name: 'app_logout')]

final class Logout extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        $request->getSession()->clear();

        return $this->redirectToRoute('app_login');
    }
}

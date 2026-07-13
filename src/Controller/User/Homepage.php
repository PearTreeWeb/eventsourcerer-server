<?php

declare(strict_types=1);

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'homepage')]
final class Homepage extends AbstractController
{
    public function __invoke(): RedirectResponse
    {
        return $this->redirectToRoute('dashboard');
    }
}

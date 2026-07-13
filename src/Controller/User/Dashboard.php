<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Domain\Widget\Query\GetAllRegisteredProjections;
use App\Infrastructure\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard', name: 'dashboard')]
final class Dashboard extends AbstractController
{
    public function __construct(private readonly QueryBus $queryBus) {}

    public function __invoke(): Response
    {
        return $this->render(
            'UI/user/dashboard.html.twig',
            [
                'widgets' => $this->queryBus->query(new GetAllRegisteredProjections()),
            ]
        );
    }
}

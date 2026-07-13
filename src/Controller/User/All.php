<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Domain\User\Query\GetAllUsers;
use App\Infrastructure\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/user', name: 'users')]
final class All extends AbstractController
{
    public function __construct(private readonly QueryBus $queryBus) {}

    public function __invoke(): Response
    {
        return $this->render(
            'UI/user/all.html.twig',
            [
                'currentUser' => $this->getUser(),
                'users'       => $this->queryBus->query(new GetAllUsers())
            ]
        );
    }
}

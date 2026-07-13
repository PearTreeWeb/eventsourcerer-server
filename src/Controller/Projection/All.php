<?php

declare(strict_types=1);

namespace App\Controller\Projection;

use App\Domain\Projection\Query\GetProjections;
use App\Entity\User;
use App\Infrastructure\SymfonyQueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/dashboard/projection', name: 'projections')]
final class All extends AbstractController
{
    public function __construct(private readonly SymfonyQueryBus $queryBus) {}

    public function __invoke(#[CurrentUser] User $user): Response
    {
        return $this->render(
            'UI/projection/all.html.twig',
            ['projections' => $this->queryBus->query(new GetProjections())]
        );
    }
}

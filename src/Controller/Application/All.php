<?php

declare(strict_types=1);

namespace App\Controller\Application;

use App\Domain\Application\Query\GetAllApplications;
use App\Entity\User;
use App\Infrastructure\SymfonyQueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/application', name: 'applications')]
#[IsGranted('ROLE_SUPER_USER')]
final class All extends AbstractController
{
    public function __construct(private readonly SymfonyQueryBus $queryBus) {}

    public function __invoke(#[CurrentUser] User $user): Response
    {
        $applications = $this->queryBus->query(new GetAllApplications());

        return $this->render(
            'UI/application/all.html.twig',
            [
                'applications' => $applications,
            ]
        );
    }
}

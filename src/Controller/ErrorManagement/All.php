<?php

namespace App\Controller\ErrorManagement;

use App\Domain\Common\Query\GetRuntimeErrors;
use App\Infrastructure\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/error-management/{page}', name: 'error-management', defaults: ['page' => 1])]
#[IsGranted('ROLE_SUPER_USER')]
final class All extends AbstractController
{
    private const int PER_PAGE = 10;

    public function __construct(private readonly QueryBus $queryBus) {}

    public function __invoke(int $page): Response
    {
        $start = ($page -1) * self::PER_PAGE;

        $errors = $this->queryBus->query(new GetRuntimeErrors($start, self::PER_PAGE));

        return $this->render('UI/error-management/all.html.twig', [
            'currentPage' => $page,
            'errors' => $errors,
            'pages' => (int) ceil($errors->count() / self::PER_PAGE),
        ]);
    }
}

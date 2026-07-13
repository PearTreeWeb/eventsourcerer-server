<?php

declare(strict_types=1);

namespace App\Controller\Stream;

use ApiPlatform\Doctrine\Orm\Paginator;
use App\Domain\Stream\Query\GetAllStreamsPaginated;
use App\Entity\Stream;
use App\Form\Common\SearchType;
use App\Infrastructure\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/streams/all/{page}/{search}', name: 'streams', defaults: ['page' => 1, 'search' => null])]
final class All extends AbstractController
{
    private const int PER_PAGE = 10;

    public function __construct(private readonly QueryBus $queryBus) {}

    public function __invoke(Request $request, int $page, ?string $search): Response
    {
        $start      = ($page -1) * self::PER_PAGE;
        $searchForm = $this->createForm(SearchType::class);

        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            return $this->redirectToRoute(
                'streams',
                [
                    'page'   => 1,
                    'search' => trim($searchForm->getData()['search']),
                ]
            );
        }

        /** @var Paginator<Stream> $streams */
        $streams = $this->queryBus->query(new GetAllStreamsPaginated($start, self::PER_PAGE, $search));

        return $this->render(
            'UI/stream/all.html.twig',
            [
                'count'       => $streams->count(),
                'currentPage' => $page,
                'pages'       => (int) ceil($streams->count() / self::PER_PAGE),
                'search'      => $search,
                'searchForm'  => $searchForm,
                'streams'     => $streams,
            ]
        );
    }
}

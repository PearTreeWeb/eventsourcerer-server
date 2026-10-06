<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Domain\Author\Repository\AuthorRepository;
use App\Domain\Event\Query\GetAllEventsPaginated;
use App\Entity\User;
use App\Extension\Default\Author\EventSourcerer;
use App\Form\Common\SearchType;
use App\Infrastructure\SymfonyQueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/dashboard/event/all/{page}/{search}/{authors}', name: 'events', defaults: ['page' => 1, 'search' => null, 'authors' => null])]
final class All extends AbstractController
{
    private const int PER_PAGE = 10;

    public function __construct(
        private readonly SymfonyQueryBus $queryBus,
        private readonly AuthorRepository $authorRepository
    ) {}

    public function __invoke(#[CurrentUser] User $user, int $page, ?string $search, ?string $authors, Request $request): Response
    {
        $start      = ($page - 1) * self::PER_PAGE;
        $searchForm = $this->createForm(SearchType::class);

        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            return $this->redirectToRoute(
                'events',
                [
                    'page'    => 1,
                    'search'  => trim($searchForm->getData()['search']),
                    'authors' => $authors,
                ]
            );
        }

        $allAuthors = $this->authorRepository->all();
        $authorIds  = ($authors && $authors !== 'null' && $authors !== 'EMPTY_AUTHORS') ? explode(',', $authors) : [EventSourcerer::id()->toString()];

        $search = ($search === 'null' || $search === '' || $search === 'EMPTY_SEARCH') ? null : $search;

        $events = $this->queryBus->query(new GetAllEventsPaginated($start, self::PER_PAGE, $search, $authorIds));

        if ($request->headers->has('Turbo-Frame')) {
            return $this->render(
                'UI/event/_events_list.html.twig',
                [
                    'events'            => $events,
                    'pages'             => (int) ceil($events->count() / self::PER_PAGE),
                    'currentPage'       => $page,
                    'search'            => $search,
                    'selectedAuthorIds' => $authorIds,
                    'authors'           => $allAuthors,
                    'count'             => $events->count(),
                    'searchForm'        => $searchForm,
                ]
            );
        }

        return $this->render(
            'UI/event/all.html.twig',
            [
                'count'             => $events->count(),
                'currentPage'       => $page,
                'events'            => $events,
                'pages'             => (int) ceil($events->count() / self::PER_PAGE),
                'searchForm'        => $searchForm,
                'search'            => $search,
                'authors'           => $allAuthors,
                'selectedAuthorIds' => $authorIds,
            ]
        );
    }
}

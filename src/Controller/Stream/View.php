<?php

declare(strict_types=1);

namespace App\Controller\Stream;

use App\Domain\Stream\Query\GetStreamEvents;
use App\Entity\StreamEvent;
use App\Infrastructure\QueryBus;
use App\ValueResolver\StreamIdResolver;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;

#[Route('/dashboard/streams/{id}/{page}', name: 'view_stream', defaults: ['page' => 1])]
final class View extends AbstractController
{
    private const int PER_PAGE = 10;

    public function __construct(private readonly QueryBus $queryBus) {}

    public function __invoke(#[ValueResolver(StreamIdResolver::class)] StreamId $id, int $page): Response
    {
        $start = ($page -1) * self::PER_PAGE;

        $events = $this->queryBus->query(GetStreamEvents::withStreamId($id, $start, self::PER_PAGE));

        return $this->render(
            'UI/stream/view.html.twig',
            [
                'currentPage' => $page,
                'events'      => $events,
                'id'          => $id->toString(),
                'isAllStream' => $id->isAllStream(),
                'pages'       => (int) ceil($events->count() / self::PER_PAGE),
            ]
        );
    }
}

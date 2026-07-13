<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Domain\Event\Model\EventId;
use App\Domain\Event\Query\GetEvent;
use App\Form\Event\ViewEventType;
use App\Infrastructure\SymfonyQueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/event/{id}/view', name: 'view_event')]
final class View extends AbstractController
{
    public function __construct(private readonly SymfonyQueryBus $queryBus) {}

    public function __invoke(EventId $id, Request $request): Response
    {
        $event = $this->queryBus->query(GetEvent::withId($id));
        $form = $this->createForm(ViewEventType::class, $event);

        return $this->render(
            'UI/event/view.html.twig',
            [
                'form' => $form,
            ]
        );
    }
}

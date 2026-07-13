<?php

declare(strict_types=1);

namespace App\Controller\Application\Checkpoint;

use App\Domain\Application\Command\ResetAllCheckpoints;
use App\Infrastructure\QueryBus;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(
    '/dashboard/application/{applicationId}/checkpoint/reset-all',
    name: 'reset_all_application_checkpoints',
    requirements: ['applicationId' => Requirement::UUID],
    methods: [Request::METHOD_GET, Request::METHOD_POST]
)]
final class ResetAll extends AbstractController
{
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'All checkpoints have been reset';

    public function __construct(private readonly MessageBusInterface $commandBus) {}

    public function __invoke(ApplicationId $applicationId): Response
    {
        $this->commandBus->dispatch(
            ResetAllCheckpoints::for($applicationId)
        );

        $this->addFlash('success', self::SUCCESS_MSG_TRANSLATION_KEY);

        return $this->redirectToRoute('manage_application_checkpoints', ['id' => $applicationId->toString()]);
    }
}

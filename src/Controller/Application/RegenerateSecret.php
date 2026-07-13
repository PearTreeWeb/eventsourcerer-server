<?php

declare(strict_types=1);

namespace App\Controller\Application;

use App\Domain\Application\Command\RegenerateApplicationSecret;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/application/{id}/regenerate-secret', name: 'regenerate_application_secret', methods: ['POST'])]
#[IsGranted('ROLE_SUPER_USER')]
final class RegenerateSecret extends AbstractController
{
    public function __construct(private readonly MessageBusInterface $commandBus) {}

    public function __invoke(ApplicationId $id): Response
    {
        try {
            $envelope = $this->commandBus->dispatch(new RegenerateApplicationSecret($id));
            $secret = $envelope->last(HandledStamp::class)?->getResult();

            $this->addFlash('success', 'Your new application secret is: ' . $secret . '. Please save it as it will not be shown again.');
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Failed to regenerate application secret: ' . $e->getMessage());
        }

        return $this->redirectToRoute('edit_application', ['id' => $id->toString()]);
    }
}

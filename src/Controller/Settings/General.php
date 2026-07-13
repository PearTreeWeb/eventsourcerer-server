<?php

namespace App\Controller\Settings;

use App\Domain\Settings\Command\UpdateSettings;
use App\Domain\Settings\Query\GetSettings;
use App\Form\Settings\PublicSshKeyType;
use App\Infrastructure\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/settings', name: 'settings_general', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_SUPER_USER')]
final class General extends AbstractController
{
    private const string SUCCESS_MSG_TRANSLATION_KEY = 'settings_updated_successfully';

    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly QueryBus $queryBus,
    ) {}

    public function __invoke(Request $request): Response
    {
        $settings = $this->queryBus->query(new GetSettings());

        $publicSshKeyForm = $this->createForm(PublicSshKeyType::class, $settings?->getPublicSshKey());

        $publicSshKeyForm->handleRequest($request);

        if ($publicSshKeyForm->isSubmitted() && $publicSshKeyForm->isValid()) {
            $this->commandBus->dispatch(new UpdateSettings($publicSshKeyForm->getData()));

            $this->addFlash('success', self::SUCCESS_MSG_TRANSLATION_KEY);

            return $this->redirectToRoute('settings_general');
        }

        return $this->render(
            'UI/settings/all.html.twig',
            [
                'forms' => [
                    'publicSshKey' => $publicSshKeyForm->createView(),
                ],
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Setup;

use App\Setup\InstallStateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/setup', name: 'setup')]
final class Index extends AbstractController
{
    public function __construct(private readonly InstallStateService $installState) {}

    public function __invoke(Request $request): Response
    {
        if ($request->query->has('reset')) {
            $session = $request->hasSession() ? $request->getSession() : null;
            $sessionId = $session?->getId() ?? 'anonymous';
            $progressFile = $this->getParameter('kernel.project_dir') . '/var/setup_progress_' . $sessionId . '.json';
            if (file_exists($progressFile)) {
                @unlink($progressFile);
            }
            $this->installState->reset();

            return $this->redirectToRoute('setup');
        }

        return $this->render('UI/setup/index.html.twig', [
            'isInstalled' => $this->installState->isInstalled(),
        ]);
    }
}

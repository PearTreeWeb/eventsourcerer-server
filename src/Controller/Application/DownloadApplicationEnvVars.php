<?php

declare(strict_types=1);

namespace App\Controller\Application;

use App\Domain\Application\Query\GetApplication;
use App\Infrastructure\QueryBus;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/download/application/{id}/env-vars', name: 'download_application_env_vars')]
#[IsGranted('ROLE_SUPER_USER')]
final class DownloadApplicationEnvVars extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    public function __invoke(ApplicationId $id): Response
    {
        $application = $this->queryBus->query(new GetApplication($id));

        $content = implode("\n", [
            'EVENTSOURCERER_APPLICATION_ID=' . $application->applicationId()->toString(),
            'EVENTSOURCERER_HOST=0.0.0.0',
            'EVENTSOURCERER_PORT=1984',
            'EVENTSOURCERER_URL=https://eventsourcerer.docker.localhost',
            'EVENTSOURCERER_SECURE=true',
            'EVENTSOURCERER_LOCAL_CERTIFICATE_DIRECTORY=certs',
            'EVENTSOURCERER_VERIFY_PEER=true',
            'EVENTSOURCERER_VERIFY_PEER_NAME=false',
            'EVENTSOURCERER_ALLOW_SELF_SIGNED=true',
            'EVENTSOURCERER_CERTIFICATE_AUTHORITY_FILE=rootCA.pem',
        ]) . "\n";

        $response = new Response($content);

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            '.env',
        );

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }
}

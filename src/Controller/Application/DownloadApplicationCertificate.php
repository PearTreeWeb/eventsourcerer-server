<?php

namespace App\Controller\Application;

use App\Domain\Application\Query\GetApplication;
use App\Domain\Application\Service\CertificateGenerator;
use App\Infrastructure\QueryBus;
use League\Flysystem\FilesystemReader;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/download/application/{id}/certificate', name: 'download_application_certificate')]
#[IsGranted('ROLE_SUPER_USER')]
final class DownloadApplicationCertificate extends AbstractController
{
    public function __construct(
        private readonly FilesystemReader $appCertsClient,
        private readonly CertificateGenerator $certificateGenerator,
        private readonly QueryBus $queryBus,
    ) {
    }

    public function __invoke(ApplicationId $id): Response
    {
        $application = $this->queryBus->query(new GetApplication($id));
        $filename = $this->certificateGenerator->certFilename($application->hostnameValueObject());

        $response = new StreamedResponse(function() use ($filename) {
            $outputStream = fopen('php://output', 'wb');

            stream_copy_to_stream($this->appCertsClient->readStream($filename), $outputStream);
        });

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename,
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}

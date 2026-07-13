<?php

namespace App\Controller\Application;

use League\Flysystem\FilesystemReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/download/certificate-authority-certificate', name: 'download_certificate_authority_certificate')]
#[IsGranted('ROLE_SUPER_USER')]
final class DownloadCertificateAuthorityCertificate extends AbstractController
{
    public function __construct(
        private readonly FilesystemReader $appCertsClient,
        private readonly string $socketCAFilename,
    ) {
    }

    public function __invoke(): Response
    {
        $response = new StreamedResponse(function() {
            $outputStream = fopen('php://output', 'wb');
            stream_copy_to_stream($this->appCertsClient->readStream($this->socketCAFilename), $outputStream);
        });

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $this->socketCAFilename,
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
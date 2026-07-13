<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\Application\Model\Hostname;
use App\Domain\Application\Service\CertificateGenerator;
use App\Entity\Application;
use Symfony\Component\Process\Process;

final readonly class MkcertCertificateGenerator implements CertificateGenerator
{
    public function __construct(
        private string $certsFolder,
    ) {}

    public function generateForApplication(Application $application): void
    {
        $domain = $application->hostname() ?: str_replace(' ', '-', $application->name())
                |> strtolower(...)
                |> (fn ($applicationName) => sprintf('%s.docker.localhost', $applicationName));

        $this->generateForHostname(Hostname::fromString($domain));
    }

    public function certFilename(Hostname $hostname): string
    {
        return $hostname->toString() . '.pem';
    }

    public function keyFilename(Hostname $hostname): string
    {
        return $hostname->toString() . '-key.pem';
    }

    public function generateForHostname(Hostname $hostname): void
    {
        $process = new Process([
            'mkcert',
            '-cert-file',
            sprintf('%s/%s', $this->certsFolder, $this->certFilename($hostname)),
            '-key-file',
            sprintf('%s/%s', $this->certsFolder, $this->keyFilename($hostname)),
            $hostname->toString(),
            'localhost',
            '127.0.0.1'
        ]);

        $process->mustRun();
    }
}

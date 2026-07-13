<?php

declare(strict_types=1);

namespace App\Domain\Application\Service;

use App\Domain\Application\Model\Hostname;
use App\Entity\Application;

interface CertificateGenerator
{
    public function generateForApplication(Application $application): void;

    public function generateForHostname(Hostname $hostname): void;

    public function certFilename(Hostname $hostname): string;

    public function keyFilename(Hostname $hostname): string;
}

<?php

declare(strict_types=1);

namespace App\Domain\Settings\Service;

interface SshKeyConverter
{
    public function convertToPem(string $sshPublicKey): string;
}

<?php

declare(strict_types=1);

namespace App\Setup\Step;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;

final readonly class GenerateJwtKeysStep implements SetupStepInterface
{
    public function __construct(
        private string $projectDir,
        private LoggerInterface $logger,
        private ?string $jwtSecretKey = null,
        private ?string $jwtPublicKey = null,
    ) {}

    public function label(): string
    {
        return 'Generate JWT keypair';
    }

    public function run(Request $request): SetupStepResult
    {
        if (empty($this->jwtSecretKey) || empty($this->jwtPublicKey)) {
            return SetupStepResult::failure('JWT key paths are not configured. Please check JWT_SECRET_KEY and JWT_PUBLIC_KEY in your .env file.');
        }

        $process = new Process(
            ['php', 'bin/console', 'lexik:jwt:generate-keypair', '--skip-if-exists'],
            $this->projectDir,
            ['JWT_SECRET_KEY' => $this->jwtSecretKey, 'JWT_PUBLIC_KEY' => $this->jwtPublicKey]
        );

        $process->run();

        if (!$process->isSuccessful()) {
            $this->logger->error('Failed to generate JWT keypair', [
                'error' => $process->getErrorOutput(),
                'output' => $process->getOutput(),
            ]);

            return SetupStepResult::failure('Failed to generate JWT keypair. Please check the logs for details.');
        }

        return SetupStepResult::success('JWT keypair generated (or already exists).');
    }
}

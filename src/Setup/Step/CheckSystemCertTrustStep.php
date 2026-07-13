<?php

declare(strict_types=1);

namespace App\Setup\Step;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;

final readonly class CheckSystemCertTrustStep implements SetupStepInterface
{
    public function __construct(private string $projectDir, private string $socketCAFilename) {}

    public function label(): string
    {
        return 'Check CA Trust in System';
    }

    public function run(Request $request): SetupStepResult
    {
        $caPath = $this->projectDir . '/certs/' . $this->socketCAFilename;

        if (!file_exists($caPath)) {
            return SetupStepResult::failure('Root CA file not found at ' . $caPath);
        }

        $userAgent = $request->headers->get('User-Agent', '');
        $clientOS = $this->detectClientOS($userAgent);

        // If the client OS differs from the server OS (e.g. Mac client, Linux server in Docker),
        // we cannot reliably verify trust on the user's actual machine from here. In that case,
        // we skip the server-side check and just instruct the user.
        if ($clientOS !== 'Unknown' && $clientOS !== PHP_OS_FAMILY) {
            $instruction = match ($clientOS) {
                'Darwin' => 'add ' . $caPath . ' to your Mac System keychain and mark it as trusted.',
                'Windows' => 'add ' . $caPath . ' to the "Trusted Root Certification Authorities" store.',
                'Linux' => 'add ' . $caPath . ' to your Linux system trust store (e.g. /usr/local/share/ca-certificates/) and run update-ca-certificates.',
                default => 'ensure ' . $caPath . ' is trusted by your ' . $clientOS . ' system.',
            };

            return SetupStepResult::success(
                'Server is running on ' . PHP_OS_FAMILY . ' (likely Docker) so trust cannot be verified ' .
                'against your host machine. Please manually ' . $instruction
            );
        }

        return match (PHP_OS_FAMILY) {
            'Darwin' => $this->checkMacOS($caPath),
            'Linux' => $this->checkLinux($caPath),
            'Windows' => $this->checkWindows($caPath),
            default => SetupStepResult::success('Skipped server-side CA trust check (unsupported OS: ' . PHP_OS_FAMILY . ').'),
        };
    }

    private function detectClientOS(string $userAgent): string
    {
        if (str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS X')) {
            return 'Darwin';
        }
        if (str_contains($userAgent, 'Windows')) {
            return 'Windows';
        }
        if (str_contains($userAgent, 'Linux')) {
            return 'Linux';
        }

        return 'Unknown';
    }

    private function checkMacOS(string $caPath): SetupStepResult
    {
        $process = new Process(['security', 'verify-cert', '-c', $caPath]);
        $process->run();

        if (!$process->isSuccessful()) {
            return SetupStepResult::failure(
                'The Root CA is not trusted in your keychain. Please add ' . $caPath . ' to your System keychain and trust it.'
            );
        }

        return SetupStepResult::success('Root CA is trusted in the keychain.');
    }

    private function checkLinux(string $caPath): SetupStepResult
    {
        // On Linux, we use openssl to verify the certificate against system default trust store.
        // We use -CApath /etc/ssl/certs as a common default, or just let openssl use its defaults.
        $process = new Process(['openssl', 'verify', $caPath]);
        $process->run();

        if (!$process->isSuccessful()) {
            return SetupStepResult::failure(
                'The Root CA is not trusted by your system. Please add ' . $caPath . ' to your system trust store (e.g., /usr/local/share/ca-certificates/ or /etc/pki/ca-trust/source/anchors/) and update the CA bundle.'
            );
        }

        return SetupStepResult::success('Root CA is trusted by the system.');
    }

    private function checkWindows(string $caPath): SetupStepResult
    {
        // On Windows, we can use certutil to check if the certificate is in the Root store.
        // -verify -urlfetch can be slow or fail without network, but we just want to see if it's in the store.
        // Another way is to check if it's in the store by its thumbprint or just try to verify.
        $process = new Process(['certutil', '-verify', $caPath]);
        $process->run();

        // certutil -verify returns 0 if valid and trusted.
        if (!$process->isSuccessful()) {
            return SetupStepResult::failure(
                'The Root CA is not trusted by your system. Please add ' . $caPath . ' to the "Trusted Root Certification Authorities" store.'
            );
        }

        return SetupStepResult::success('Root CA is trusted by the system.');
    }
}

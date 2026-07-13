<?php

declare(strict_types=1);

namespace App\Command\Setup;

use App\Domain\Application\Model\Hostname;
use App\Domain\Application\Service\CertificateGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:setup:create_certificates', description: 'Creates initial certificates')]
final readonly class CreateCertificates
{
    private const int PRIVATE_KEY_BITS = 2048;
    private const int CA_DAYS_BEFORE_EXPIRY = 3650; // 10 years

    public function __construct(
        private string $certsFolder,
        private string $socketCAFilename,
        private string $socketCAKeyFilename,
        private string $socketApplicationHostname,
        private CertificateGenerator $certificateGenerator,
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $this->createCertificateAuthority();
        $this->createApplicationCertificate();

        $io->success('Certificate Authority created');

        return Command::SUCCESS;
    }

    private function createCertificateAuthority(): void
    {
        $caKey  = openssl_pkey_new(['private_key_bits' => self::PRIVATE_KEY_BITS, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $caCsr  = openssl_csr_new(['CN' => 'EventSourcerer Root CA'], $caKey);
        $caCert = openssl_csr_sign($caCsr, null, $caKey, self::CA_DAYS_BEFORE_EXPIRY, [
            'digest_alg'      => 'sha256',
            'x509_extensions' => 'v3_ca',
            'config'          => $this->writeTempCaOpenSslConfig(),
        ]);

        openssl_x509_export_to_file($caCert, sprintf('%s/%s', $this->certsFolder, $this->socketCAFilename));
        openssl_pkey_export_to_file($caKey, sprintf('%s/%s', $this->certsFolder, $this->socketCAKeyFilename));
    }

    private function writeTempCaOpenSslConfig(): string
    {
        $config = "[req]\n"
            . "distinguished_name = req_distinguished_name\n"
            . "prompt = no\n\n"
            . "[req_distinguished_name]\n\n"
            . "[v3_ca]\n"
            . "basicConstraints = critical,CA:TRUE\n"
            . "keyUsage = critical,keyCertSign,cRLSign\n"
            . "subjectKeyIdentifier = hash\n";

        $path = tempnam(sys_get_temp_dir(), 'openssl_ca_');
        file_put_contents($path, $config);

        return $path;
    }

    private function createApplicationCertificate(): void
    {
        $this->certificateGenerator->generateForHostname(Hostname::fromString($this->socketApplicationHostname));
    }
}

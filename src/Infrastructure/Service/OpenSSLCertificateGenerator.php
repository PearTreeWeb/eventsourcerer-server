<?php

namespace App\Infrastructure\Service;

use App\Domain\Application\Model\Hostname;
use App\Domain\Application\Service\CertificateGenerator;
use App\Entity\Application;
use League\Flysystem\FilesystemReader;

final readonly class OpenSSLCertificateGenerator implements CertificateGenerator
{
    private const int EXPIRE_AFTER_N_DAYS = 365;

    public function __construct(
        private FilesystemReader $appCertsClient,
        private string $certsFolder,
        private string $socketCAFilename,
        private string $socketCAKeyFilename,
    ) {}

    public function generateForApplication(Application $application): void
    {
        $this->generateForHostname($application->hostnameValueObject());
    }

    private function certPath(Hostname $hostname): string
    {
        return sprintf('%s/%s', $this->certsFolder, $this->certFilename($hostname));
    }

    private function certKeyPath(Hostname $hostname): string
    {
        return sprintf('%s/%s', $this->certsFolder, $this->keyFilename($hostname));
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
        $clientKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'config'           => '/dev/null',
        ]);

        $san = implode(',', array_unique([
            'DNS:' . $hostname->toString(),
            'DNS:*.docker.localhost',
            'DNS:localhost',
            'IP:0.0.0.0',
        ]));
        $configFile = $this->writeTempOpenSslConfig($san);

        try {
            $clientCsr = openssl_csr_new(
                ['CN' => $hostname->toString()],
                $clientKey,
                ['config' => $configFile, 'req_extensions' => 'v3_req']
            );

            $caCert = openssl_x509_read($this->appCertsClient->read($this->socketCAFilename));
            $caKey = openssl_pkey_get_private($this->appCertsClient->read($this->socketCAKeyFilename));

            $clientCert = openssl_csr_sign(
                $clientCsr,
                $caCert,
                $caKey,
                self::EXPIRE_AFTER_N_DAYS,
                ['config' => $configFile, 'x509_extensions' => 'v3_req', 'digest_alg' => 'sha256']
            );

            openssl_x509_export_to_file($clientCert, $this->certPath($hostname));
            openssl_pkey_export_to_file($clientKey, $this->certKeyPath($hostname), null, ['config' => '/dev/null']);
        } finally {
            @unlink($configFile);
        }
    }

    private function writeTempOpenSslConfig(string $san): string
    {
        $config = "[req]\n"
            . "distinguished_name = req_distinguished_name\n"
            . "req_extensions = v3_req\n"
            . "prompt = no\n\n"
            . "[req_distinguished_name]\n\n"
            . "[v3_req]\n"
            . "subjectAltName = $san\n"
            . "basicConstraints = CA:FALSE\n"
            . "keyUsage = critical,digitalSignature,keyEncipherment\n"
            . "extendedKeyUsage = serverAuth\n";

        $path = tempnam(sys_get_temp_dir(), 'openssl_');
        file_put_contents($path, $config);

        return $path;
    }
}

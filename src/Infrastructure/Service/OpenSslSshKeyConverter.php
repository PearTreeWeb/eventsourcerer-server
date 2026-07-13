<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\Settings\Service\SshKeyConverter;

final readonly class OpenSslSshKeyConverter implements SshKeyConverter
{
    public function convertToPem(string $sshPublicKey): string
    {
        $sshPublicKey = trim($sshPublicKey);

        if (str_starts_with($sshPublicKey, '-----BEGIN PUBLIC KEY-----')) {
            return $sshPublicKey;
        }

        // Handle cases where multiple lines might be present (though trim already handles start/end)
        $sshPublicKey = str_replace(["\r", "\n"], '', $sshPublicKey);

        // Standard SSH public key format: "ssh-rsa <base64> [comment]"
        // If it's just the base64 part, we might need to be careful, but usually it's the full string.
        $parts = explode(' ', $sshPublicKey);
        if (count($parts) < 2) {
            // Check if it's just the base64 part of an RSA key
            $keyData = base64_decode($sshPublicKey, true);
            if ($keyData && str_starts_with($keyData, "\x00\x00\x00\x07ssh-rsa")) {
                $type = 'ssh-rsa';
            } else {
                return $sshPublicKey;
            }
        } else {
            $type = $parts[0];
            $keyData = base64_decode($parts[1]);
        }

        if (!$keyData) {
            return $sshPublicKey;
        }

        if ($type !== 'ssh-rsa') {
            throw new \RuntimeException(sprintf('Unsupported SSH key type: %s. Only ssh-rsa is supported for personal data encryption. Please update your SSH key in Settings.', $type));
        }

        $readString = function (&$data) {
            $len = unpack('N', substr($data, 0, 4))[1];
            $str = substr($data, 4, $len);
            $data = substr($data, 4 + $len);
            return $str;
        };

        $innerType = $readString($keyData);
        if ($innerType !== 'ssh-rsa') {
            return $sshPublicKey;
        }

        $publicExponent = $readString($keyData);
        $modulus = $readString($keyData);

        $encodeDerInteger = function ($bytes) {
            $bytes = ltrim($bytes, "\x00");
            if (ord($bytes[0]) & 0x80) {
                $bytes = "\x00" . $bytes;
            }
            return "\x02" . $this->encodeDerLength(strlen($bytes)) . $bytes;
        };

        $rsaPublicKey = "\x30" . $this->encodeDerLength(strlen($encodeDerInteger($modulus)) + strlen($encodeDerInteger($publicExponent))) . $encodeDerInteger($modulus) . $encodeDerInteger($publicExponent);

        $rsaOid = "\x30\x0d\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00";
        $bitString = "\x03" . $this->encodeDerLength(strlen($rsaPublicKey) + 1) . "\x00" . $rsaPublicKey;

        $totalLength = strlen($rsaOid) + strlen($bitString);
        $der = "\x30" . $this->encodeDerLength($totalLength) . $rsaOid . $bitString;

        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64) . "-----END PUBLIC KEY-----";
    }

    private function encodeDerLength(int $length): string
    {
        if ($length <= 0x7F) {
            return chr($length);
        }
        $temp = ltrim(pack('N', $length), "\x00");
        return chr(0x80 | strlen($temp)) . $temp;
    }
}

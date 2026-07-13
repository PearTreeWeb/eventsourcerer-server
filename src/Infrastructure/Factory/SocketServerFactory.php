<?php

declare(strict_types=1);

namespace App\Infrastructure\Factory;

use Evenement\EventEmitterInterface;
use React\Socket\SecureServer;
use React\Socket\SocketServer;

final readonly class SocketServerFactory
{
    public static function create(
        string $cert,
        string $certKey,
        string $caFile,
        string $socketHost,
        int $socketPort,
        bool $socketSecure
    ): ?EventEmitterInterface {
        try {
            if (!$socketSecure) {
                return new SocketServer(
                    self::uri($socketHost, $socketPort),
                    [
                        'tcp_nodelay' => true,
                    ]
                );
            }

            return new SecureServer(
                new SocketServer(self::uri($socketHost, $socketPort)),
                null,
                [
                    'local_cert' => $cert,
                    'local_pk' => $certKey,
                    'cafile' => $caFile,
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            );
        } catch (\Throwable) {
            // server already started
        }

        return null;
    }

    public static function uri(string $socketHost, int $socketPort): string
    {
        return sprintf('%s:%d', $socketHost, $socketPort);
    }
}

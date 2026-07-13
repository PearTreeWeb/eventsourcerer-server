<?php

declare(strict_types=1);

namespace App\Setup\Step;

use App\Infrastructure\Factory\SocketServerFactory;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final readonly class CheckSocketServerStep implements SetupStepInterface
{
    public function __construct(
        private string $socketHost,
        private int $socketPort,
        private string $socketCaFile,
        private bool $socketSecure = false,
    ) {}

    public function label(): string
    {
        return 'Check socket server';
    }

    public function run(Request $request): SetupStepResult
    {
        try {
            $context = null;
            if ($this->socketSecure) {
                $context = stream_context_create([
                    'ssl' => [
                        'cafile' => $this->socketCaFile,
                        'allow_self_signed' => true,
                        'verify_peer' => true,
                        'verify_peer_name' => false,
                    ],
                ]);
            }

            $address = ($this->socketSecure ? 'tls://' : '') . SocketServerFactory::uri($this->socketHost, $this->socketPort);

            $connection = @stream_socket_client(
                address: $address,
                error_code: $errorCode,
                error_message: $errorMessage,
                timeout: 2,
                flags: STREAM_CLIENT_CONNECT,
                context: $context,
            );

            if (false === $connection) {
                return SetupStepResult::failure(
                    sprintf('Could not connect to the socket server at %s: %s', $address, $errorMessage)
                );
            }

            fclose($connection);

            return SetupStepResult::success('Socket server is running and accepting connections.');
        } catch (Throwable $e) {
            return SetupStepResult::failure('Error checking socket server: ' . $e->getMessage());
        }
    }
}

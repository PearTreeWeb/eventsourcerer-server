<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Common\Service\EventBroadcaster;
use App\Infrastructure\Exception\CouldNotBroadcastEvent;
use App\Infrastructure\Factory\SocketServerFactory;
use Exception;
use PearTreeWeb\EventSourcerer\Common\Model\MessageType;
use Psr\Log\LoggerInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use React\Socket\SecureConnector;

final readonly class SocketEventBroadcaster implements EventBroadcaster
{
    private const int MAX_ATTEMPTS_TO_BROADCAST = 3;

    public function __construct(
        private string $socketHost,
        private int $socketPort,
        private LoggerInterface $socketLogger,
        /** @phpstan-ignore property.onlyWritten */
        private string $socketCert,
        /** @phpstan-ignore property.onlyWritten */
        private string $socketCertKey,
        private string $socketCaFile,
        private bool $socketSecure = false,
    ) {}

    public function broadcast(string $message): void
    {
        $secureConnector = null;
        $connector = new Connector();

        if ($this->socketSecure) {
            $secureConnector = new SecureConnector(
                $connector,
                null,
                [
                    'cafile' => $this->socketCaFile,
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            );
        }

        ($secureConnector ?? $connector)
            ->connect(SocketServerFactory::uri($this->socketHost, $this->socketPort))
            ->then($this->handleConnection($message), $this->handleRejection())
            ->catch($this->handleRejection());
    }

    public function broadcastSync(string|\Stringable $message, ?int $attempt = 1): void
    {
        try {
            $context = null;
            if ($this->socketSecure) {
                $context = stream_context_create([
                    'ssl' => [
                        'cafile' => $this->socketCaFile,
                        'allow_self_signed' => true,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ]);
            }

            $connection = stream_socket_client(
                address: ($this->socketSecure ? 'tls://' : '') . SocketServerFactory::uri($this->socketHost, $this->socketPort),
                error_code: $errorCode,
                error_message: $errorMessage,
                timeout: 5,
                flags: STREAM_CLIENT_CONNECT,
                context: $context,
            );

            if (false !== $connection) {
                stream_set_timeout($connection, 5);
            }

            if (false === $connection) {
                $this->handleFailedBroadcast($message, $attempt, $errorMessage);
            }

            $writeResult = fwrite($connection, self::formatEvent($message));

            fclose($connection);

            if (false === $writeResult) {
                $this->handleFailedBroadcast(
                    $message,
                    $attempt,
                    'Failed to broadcast event for unknown reason',
                );
            }
        } catch (\Throwable $e) {
            $this->handleFailedBroadcast($message, $attempt, $e->getMessage());
        }
    }

    private function handleConnection(string $message): callable
    {
        return function (ConnectionInterface $connection) use ($message): void {
            $this->sendMessage($connection, $message);
        };
    }

    private function sendMessage(ConnectionInterface $connection, string $message): void
    {
        $this->socketLogger->info('Event Broadcaster connected');

        $connection->write(self::formatEvent($message));

        $this->socketLogger->info(
            sprintf(
                'Event Broadcaster sent message to %s %s',
                $connection->getRemoteAddress(),
                $connection->getLocalAddress()
            )
        );
    }

    private function handleRejection(): callable
    {
        return function (Exception $e): void {
            $this->socketLogger->warning('Event Broadcaster could not connect: ' . $e->getMessage());
            $this->socketLogger->error($e->getMessage());
        };
    }

    public static function formatEvent(string|\Stringable $message): string
    {
        return sprintf(
            '%s %s',
            MessageType::NewEvent->value,
            $message
        );
    }

    private function handleFailedBroadcast(\Stringable|string $message, ?int $attempt, string $error): void
    {
        if (self::MAX_ATTEMPTS_TO_BROADCAST === $attempt) {
            throw CouldNotBroadcastEvent::withMessage($error);
        }

        $attempt++;

        sleep(1);

        $this->broadcastSync($message, $attempt);
    }
}

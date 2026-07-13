<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Common\Model\IpAddress;
use App\Domain\Connection\Model\Connection;
use App\Domain\Connection\Model\ConnectionType;
use App\Domain\Connection\Service\RecordConnection;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationType;
use PearTreeWeb\EventSourcerer\Common\Model\WorkerId;
use React\Socket\ConnectionInterface;

final readonly class SocketConnectionsPool
{
    /**
     * @param \SplObjectStorage<object, mixed> $clients
     */
    private function __construct(
        private \SplObjectStorage $clients,
        private RecordConnection $recordConnection
    ) {}

    public static function create(
        RecordConnection $recordConnection,
    ): self {
        return new self(new \SplObjectStorage(), $recordConnection);
    }

    public function addConnection(ConnectionInterface $connection): void
    {
        $this->clients->offsetSet($connection);
    }

    public function removeConnection(ConnectionInterface $connection): void
    {
        $this->clients->offsetUnset($connection);
    }

    public function identify(
        ConnectionInterface $connection,
        ApplicationId $applicationId,
        ApplicationType $applicationType,
        WorkerId $workerId
    ): void {
        $this->recordConnection->for(
            $applicationId,
            $applicationType,
            ConnectionType::SOCKET,
            IpAddress::fromString($connection->getRemoteAddress())
        );

        $this->clients->offsetUnset($connection);

        $this->clients->offsetSet(
            $connection,
            new Connection($applicationId, $workerId)
        );
    }

    /**
     * @return WorkerConnection[]
     */
    public function allApplicationConnections(): array
    {
        $clients = [];

        $this->clients->rewind();

        while($this->clients->valid()) {
            if (null !== $this->clients->getInfo()) {
                /** @var Connection $connection */
                $connection = $this->clients->getInfo();

                $clients[$connection->workerId->toString()] = new WorkerConnection(
                    $connection->applicationId,
                    $connection->workerId,
                    $this->clients->current(),
                );
            }

            $this->clients->next();
        }

        return $clients;
    }

    /**
     * @return ConnectionInterface[]
     */
    public function connectionsForApplicationId(ApplicationId $applicationId): array
    {
        $clients = [];

        $this->clients->rewind();

        while($this->clients->valid()) {
            /** @var Connection|null $info */
            $info = $this->clients->getInfo();

            if (
                null !== $info
                && $info->applicationId->sameAs($applicationId)
            ) {
                $clients[] = $this->clients->current();
            }

            $this->clients->next();
        }

        return $clients;
    }

    public function applicationIdFor(ConnectionInterface $conn): ?ApplicationId
    {
        $this->clients->rewind();
        while($this->clients->valid()) {
            if ($this->clients->current() === $conn) {
                /** @var Connection|null $connection */
                $connection = $this->clients->getInfo();

                if (null === $connection) {
                    return null;
                }

                return ApplicationId::fromString($connection->applicationId->toString());
            }
            $this->clients->next();
        }

        return null;
    }

    public function workerIdFor(ConnectionInterface $conn): ?WorkerId
    {
        $this->clients->rewind();
        while($this->clients->valid()) {
            if ($this->clients->current() === $conn) {
                /** @var Connection|null $connection */
                $connection = $this->clients->getInfo();

                if (null === $connection) {
                    return null;
                }

                return WorkerId::fromString($connection->workerId->toString());
            }
            $this->clients->next();
        }

        return null;
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients()->offsetUnset($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        // @todo handle this!
    }

    /**
     * @return \SplObjectStorage<object, mixed>
     */
    public function clients(): \SplObjectStorage
    {
        return $this->clients;
    }
}

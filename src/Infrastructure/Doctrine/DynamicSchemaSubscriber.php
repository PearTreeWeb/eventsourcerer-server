<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Events;

final class DynamicSchemaSubscriber implements EventSubscriber
{
    private string $subscriberId;

    public function __construct()
    {
        $this->subscriberId = $_ENV['SUBSCRIBER_ID'] ?? $_SERVER['SUBSCRIBER_ID'] ?? 'default';
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postConnect,
        ];
    }

    public function postConnect(ConnectionEventArgs $args): void
    {
        // Re-check subscriber ID in case it was set by the Request listener
        $this->subscriberId = $_ENV['SUBSCRIBER_ID'] ?? $_SERVER['SUBSCRIBER_ID'] ?? 'default';

        if ($this->subscriberId === 'default') {
            return;
        }

        // Validate subscriberId to prevent SQL injection
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $this->subscriberId)) {
            return;
        }

        $connection = $args->getConnection();
        $connection->executeStatement(sprintf('SET search_path TO %s, public', $this->subscriberId));
    }
}

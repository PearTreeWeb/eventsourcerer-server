<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;

/**
 * DBAL 4 middleware that sets the PostgreSQL search_path on every new connection
 * so that each tenant's schema is used automatically.
 *
 * For single-tenant / local installs where SUBSCRIBER_ID is not set the middleware
 * is a no-op and the default search_path is left unchanged.
 */
final class DynamicSchemaSubscriber implements Middleware
{
    public function wrap(Driver $driver): Driver
    {
        return new class($driver) extends AbstractDriverMiddleware {
            public function connect(array $params): Connection
            {
                $connection = parent::connect($params);

                // Read the subscriber ID that SubscriberIdentificationListener set
                // on the very same request.
                $subscriberId = $_ENV['SUBSCRIBER_ID'] ?? $_SERVER['SUBSCRIBER_ID'] ?? 'default';

                if ($subscriberId === 'default') {
                    return $connection;
                }

                // Validate to prevent SQL injection before interpolating into the statement.
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $subscriberId)) {
                    return $connection;
                }

                $connection->exec(sprintf('SET search_path TO %s, public', $subscriberId));

                return $connection;
            }
        };
    }
}

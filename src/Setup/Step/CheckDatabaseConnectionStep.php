<?php

declare(strict_types=1);

namespace App\Setup\Step;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final readonly class CheckDatabaseConnectionStep implements SetupStepInterface
{
    public function __construct(private Connection $connection) {}

    public function label(): string
    {
        return 'Check database connection';
    }

    public function run(Request $request): SetupStepResult
    {
        try {
            $this->connection->executeQuery('SELECT 1');

            return SetupStepResult::success('Database connection established successfully.');
        } catch (Throwable $e) {
            return SetupStepResult::failure('Could not connect to the database: ' . $e->getMessage());
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Setup\Step;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Throwable;

final readonly class RunMigrationsStep implements SetupStepInterface
{
    public function __construct(private KernelInterface $kernel) {}

    public function label(): string
    {
        return 'Run database migrations';
    }

    public function run(Request $request): SetupStepResult
    {
        try {
            $application = new Application($this->kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput([
                'command'          => 'doctrine:migrations:migrate',
                '--no-interaction' => true,
                '--allow-no-migration' => true,
                '-q' => true,
            ]);
            $output = new BufferedOutput();

            $exitCode = $application->run($input, $output);

            if ($exitCode !== 0) {
                return SetupStepResult::failure('Migrations failed: ' . trim($output->fetch()));
            }

            $log = trim($output->fetch());

            return SetupStepResult::success($log !== '' ? 'Migrations applied.' : 'Database is up to date.');
        } catch (Throwable $e) {
            return SetupStepResult::failure('Could not run migrations: ' . $e->getMessage());
        }
    }
}

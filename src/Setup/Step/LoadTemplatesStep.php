<?php

declare(strict_types=1);

namespace App\Setup\Step;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Throwable;

final readonly class LoadTemplatesStep implements SetupStepInterface
{
    public function __construct(private KernelInterface $kernel) {}

    public function label(): string
    {
        return 'Load event templates';
    }

    public function run(Request $request): SetupStepResult
    {
        try {
            $application = new Application($this->kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput([
                'command' => 'app:setup:load-templates',
            ]);
            $output = new BufferedOutput();

            $exitCode = $application->run($input, $output);

            if ($exitCode !== 0) {
                return SetupStepResult::failure('Loading templates failed: ' . trim($output->fetch()));
            }

            return SetupStepResult::success('Event templates loaded.');
        } catch (Throwable $e) {
            return SetupStepResult::failure('Could not load templates: ' . $e->getMessage());
        }
    }
}

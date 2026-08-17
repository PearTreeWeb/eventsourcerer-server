<?php

declare(strict_types=1);

namespace App\Setup\Step;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Throwable;

final readonly class LoadAuthorsStep implements SetupStepInterface
{
    public function __construct(private KernelInterface $kernel) {}

    public function label(): string
    {
        return 'Load authors';
    }

    public function run(Request $request): SetupStepResult
    {
        try {
            $application = new Application($this->kernel);
            $application->setAutoExit(false);
            $input = new ArrayInput([
                'command' => 'app:setup:load-authors',
            ]);
            $output = new BufferedOutput();
            $exitCode = $application->run($input, $output);
            if ($exitCode !== 0) {
                return SetupStepResult::failure('Loading authors failed: ' . trim($output->fetch()));
            }

            return SetupStepResult::success('Authors loaded.');
        } catch (Throwable $e) {
            return SetupStepResult::failure('Could not load authors: ' . $e->getMessage());
        }
    }
}

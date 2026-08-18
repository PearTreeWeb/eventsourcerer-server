<?php

declare(strict_types=1);

namespace App\Command\Setup;

use App\Domain\Application\Command\RegisterApplication;
use App\Domain\Application\Model\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'app:setup:load-applications')]
final readonly class LoadApplications
{
    public function __construct(
        /** @var iterable<Application> $applications */
        private iterable $applications,
        private MessageBusInterface $commandBus,
    ) {}

    public function __invoke(OutputInterface $output): int
    {
        foreach ($this->applications as $application) {
            $this->commandBus->dispatch(
                new RegisterApplication(
                    $application::id(),
                    $application::name(),
                ),
            );
        }

        $output->writeln('<info>Applications loaded.</info>');

        return Command::SUCCESS;
    }
}

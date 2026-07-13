<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\Client\Repository\StreamEventRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:events:tombstone')]
final class TombstoneEvents extends Command
{
    public function __construct(private readonly StreamEventRepository $streamEventRepository)
    {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->streamEventRepository->tombstoneEvents();

        $output->writeln('<info>Applicable events tombstoned</info>');

        return Command::SUCCESS;
    }
}

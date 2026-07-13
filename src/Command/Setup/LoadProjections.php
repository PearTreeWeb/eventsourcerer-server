<?php

namespace App\Command\Setup;

use App\Domain\Projection\Model\ProjectionName;
use App\Domain\Projection\Repository\ProjectionRepository;
use App\Extension\Default\Widget\Projection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:setup:load-projections')]
final readonly class LoadProjections
{
    public function __construct(
        /** @var iterable<Projection> $projections */
        private iterable $projections,
        private ProjectionRepository $projectionRepository
    ) {}

    public function __invoke(OutputInterface $output): int
    {
        foreach ($this->projections as $projection) {
            $projectionEntity = $projection->fetch();
            $projectionName = ProjectionName::fromString($projectionEntity->getName());

            if ($this->projectionRepository->doesNotExist($projectionName)) {
                $this->projectionRepository->create($projectionEntity);
            }
        }

        $output->writeln('<info>Projections loaded.</info>');

        return Command::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace App\Command\Setup;

use App\Domain\Author\AuthorDetails;
use App\Domain\Author\Repository\AuthorRepository;
use App\Entity\Author;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(name: 'app:setup:load-authors')]
final readonly class LoadAuthors
{
    public function __construct(
        /** @var iterable<AuthorDetails> $authors */
        private iterable $authors,
        private AuthorRepository $authorRepository
    ) {}

    public function __invoke(OutputInterface $output): int
    {
        /** @var array<string, string> $createdIds */
        $createdIds = [];

        foreach ($this->authors as $author) {
            $name = $author::author()->toString();

            $existing = $this->authorRepository->findByName($name);
            if ($existing !== null) {
                $createdIds[$name] = (string) $existing->getId();
                continue;
            }

            $entity = Author::create(Uuid::v4(), $name);
            $this->authorRepository->create($entity);
            $createdIds[$name] = (string) $entity->getId();

            $output->writeln(sprintf('<info>Author "%s" created (%s).</info>', $name, $createdIds[$name]));
        }

        $output->writeln('<info>Authors loaded.</info>');

        foreach ($createdIds as $name => $id) {
            $output->writeln(sprintf(' - %s => %s', $name, $id));
        }

        return Command::SUCCESS;
    }
}

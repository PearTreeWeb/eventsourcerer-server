<?php

namespace App\Command\Setup;

use App\Domain\Author\Repository\AuthorRepository;
use App\Domain\Event\Model\EventProperties;
use App\Domain\Event\Model\EventProperty;
use App\Domain\Event\Model\EventTemplate;
use App\Domain\Event\Repository\EventRepository;
use App\Entity\Event;
use App\Entity\EventProperty as EventPropertyEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Clock\ClockInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:setup:load-templates')]
final readonly class LoadTemplates
{
    public function __construct(
        /**
         * @var iterable<EventTemplate> $eventTemplates
         */
        private iterable $eventTemplates,
        private EventRepository $eventRepository,
        private ClockInterface $clock,
        private AuthorRepository $authorRepository,
    ) {}

    public function __invoke(OutputInterface $output): int
    {
        $now = $this->clock->now();

        foreach ($this->eventTemplates as $eventTemplate) {
            $event = Event::create(
                $eventTemplate::id(),
                $eventTemplate::name()->toString(),
                $eventTemplate::tombstoneAfterNSeconds(),
                $now
            )->setIsSystemEvent($eventTemplate::isSystemEvent());

            $event->setProperties(
                self::eventPropertyEntities(
                    $eventTemplate::eventProperties(),
                    $event,
                    $now
                )
            );

            $author = $this->authorRepository->findByName($eventTemplate::author()->toString());

            $event->setAuthorId($author?->getId());

            if (!$this->eventRepository->find($eventTemplate::id())) {
                $this->eventRepository->create($event);
            }
        }

        $output->writeln('<info>Templates loaded.</info>');

        return Command::SUCCESS;
    }

    /**
     * @return ArrayCollection<array-key, EventPropertyEntity>
     */
    private static function eventPropertyEntities(
        EventProperties $properties,
        Event $event,
        \DateTimeImmutable $now
    ): ArrayCollection {
        return new ArrayCollection(
            $properties
                ->items()
                ->map(function (EventProperty $property) use ($event, $now) {
                    return EventPropertyEntity::create(
                        $property,
                        $now
                    )->setEvent($event);
                })
                ->all()
        );
    }
}

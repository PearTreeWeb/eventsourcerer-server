<?php

declare(strict_types=1);

namespace App\Domain\Event\Handler;

use App\Domain\Event\Command\RegisterEvent;
use App\Domain\Event\Model\EventProperties;
use App\Domain\Event\Model\EventProperty as EventPropertyModel;
use App\Domain\Event\Repository\EventRepository;
use App\Entity\Event;
use App\Entity\EventProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RegisterEventHandler
{
    public function __construct(private EventRepository $eventRepository, private ClockInterface $clock) {}

    public function __invoke(RegisterEvent $command): void
    {
        $now = $this->clock->now();

        $event = Event::create(
            $command->id,
            $command->name->toString(),
            $command->tombstoneAfter,
            $this->clock->now(),
            $command->authorId
        );

        $event->setProperties(self::eventPropertyEntities($command->properties, $event, $now));

        $this->eventRepository->create($event);
    }

    /**
     * @return ArrayCollection<int, EventProperty>
     */
    private static function eventPropertyEntities(
        EventProperties $properties,
        Event $event,
        \DateTimeImmutable $now
    ): ArrayCollection {
        /** @var ArrayCollection<int, EventProperty> */
        return new ArrayCollection(
            $properties
            ->items()
            ->map(
                function (EventPropertyModel $eventProperty) use ($now, $event): EventProperty {
                    return EventProperty::create($eventProperty, $now)
                        ->setEvent($event);
                }
            )
            ->all()
        );
    }
}

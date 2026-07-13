<?php

declare(strict_types=1);

namespace App\Domain\Event\Handler;

use App\Domain\Common\Service\GenerateUuid;
use App\Domain\Event\Command\EditEvent;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventProperties;
use App\Domain\Event\Model\EventProperty as EventPropertyModel;
use App\Domain\Event\Repository\EventRepository;
use App\Entity\Event;
use App\Entity\EventProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EditEventHandler
{
    public function __construct(
        private EventRepository $eventRepository,
        private ClockInterface $clock,
        private GenerateUuid $generateUuid
    ) {}

    public function __invoke(EditEvent $command): void
    {
        $currentEvent = $this->retireCurrentEvent($command->id);
        $newEntity    = $currentEvent->newVersion($command->newId, $this->clock->now());
        $properties   = $this->properties($newEntity, $command->properties, $currentEvent->getProperties(), $this->clock->now());

        $this->eventRepository->create(
            $newEntity
                ->setProperties($properties)
                ->setName($command->name->toString())
                ->setTombstoneAfterSeconds($command->tombstoneAfter)
        );
    }

    /**
     * @param Collection<int, EventProperty> $existingProperties
     *
     * @return ArrayCollection<int, EventProperty>
     */
    private function properties(
        Event $event,
        EventProperties $newProperties,
        Collection $existingProperties,
        \DateTimeImmutable $now
    ): ArrayCollection {
        $updatedProperties = $newProperties
            ->items()
            ->map(function (EventPropertyModel $property) use ($event, $now, $existingProperties): EventProperty {
                $existingProperty = $existingProperties
                    ->findFirst(
                        static fn (int $index, EventProperty $entityProperty) => $entityProperty
                            ->id()
                            ->equals($property->id->toUuid())
                    );

                if ($existingProperty) {
                    $newProperty = $existingProperty->clone(
                        $this->generateUuid->random()
                    );

                    $newProperty
                        ->setType($property->type::name()->toString())
                        ->setTypeClass($property->type::class)
                        ->setEvent($event)
                        ->setName($property->name->toString())
                        ->setContainsPersonalData($property->containsPersonalData)
                        ->setUpdatedAt($now);
                } else {
                    $newProperty = EventProperty::create($property, $now)
                        ->setEvent($event);
                }

                /** @var EventProperty|null */
                return $newProperty;
            })
            ->filter()
            ->values()
            ->all();

        return new ArrayCollection($updatedProperties);
    }

    private function retireCurrentEvent(EventId $id): Event
    {
        $entity = $this
            ->eventRepository
            ->findStrict($id);

        $entity->setRetired(true);

        $this->eventRepository->update($entity);

        return $entity;
    }
}

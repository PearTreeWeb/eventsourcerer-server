<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\Common\Service\GenerateUuid;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Repository\EventRepository;
use App\Domain\Projection\Model\Mutation;
use App\Domain\Projection\Model\MutationConditionGroups;
use App\Domain\Projection\Model\ProjectionEventProperty;
use App\Domain\Projection\Model\ProjectionEventPropertyId;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionMutation as ProjectionMutationModel;
use App\Domain\Projection\Model\ProjectionMutationId;
use App\Domain\Projection\Model\ProjectionName;
use App\Domain\Projection\Model\ProjectionProperty as ProjectionPropertyModel;
use App\Domain\Projection\Service\ProjectionBuilder;
use App\Entity\Projection;
use App\Entity\ProjectionMutation;
use App\Entity\ProjectionProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Clock\ClockInterface;

final readonly class DoctrineProjectionBuilder implements ProjectionBuilder
{
    private function __construct(
        private ClockInterface $clock,
        private GenerateUuid $generateUuid,
        private EventRepository $eventRepository,
        private ?ProjectionName $name = null,
        /** @var Mutation[] $mutations */
        private array $mutations = [],
        /** @var ProjectionPropertyModel[] $properties */
        private array $properties = []
    ) {}

    public static function create(
        ClockInterface $clock,
        GenerateUuid $generateUuid,
        EventRepository $eventRepository,
        ProjectionName $name
    ): self {
        return new self(
            $clock,
            $generateUuid,
            $eventRepository,
            $name
        );
    }

    public function addMutation(Mutation $mutation): ProjectionBuilder
    {
        $mutations = $this->mutations;

        $mutations[] = $mutation;

        return new self(
            $this->clock,
            $this->generateUuid,
            $this->eventRepository,
            $this->name,
            $mutations,
            $this->properties
        );
    }

    public function addProperty(ProjectionPropertyModel $property): ProjectionBuilder
    {
        $properties = $this->properties;

        $properties[$property->name->toString()] = $property;

        return new self(
            $this->clock,
            $this->generateUuid,
            $this->eventRepository,
            $this->name,
            $this->mutations,
            $properties
        );
    }

    public function build(): Projection
    {
        $now = $this->clock->now();

        $projection = Projection::create(
            ProjectionId::fromUuid($this->generateUuid->for($this->name)),
            $this->name,
            true,
            true,
            false,
            $this->clock->now()
        );

        $properties = new ArrayCollection();

        foreach ($this->properties as $property) {
            $projectionEventPropertyId = ProjectionEventPropertyId::fromUuid(
                $this->generateUuid->random()
            );

            $projectionProperty = ProjectionProperty::create(
                new ProjectionEventProperty(
                    $projectionEventPropertyId,
                    EventPropertyName::fromString($property->name->toString()),
                    $property->propertyType
                )
            )
                ->setProjection($projection)
                ->setCreatedAt($now)
                ->setUpdatedAt($now);

            foreach ($this->mutations as $mutation) {
                $mutationProperty = $this->properties[$mutation->projectionPropertyName->toString()];

                if ($mutationProperty->propertyType::class === $projectionProperty->getType()) {
                    $eventEntity = $this->eventRepository->findByNameStrict(
                        $mutation->eventName,
                        $mutation->eventVersion
                    );

                    $eventProperty = $eventEntity->eventProperties()->findByName($mutation->eventPropertyName);

                    $projectionProperty = $projectionProperty->addMutation(
                        ProjectionMutation::create(
                            $projectionEventPropertyId,
                            $projection->getId(),
                            $eventEntity->getId(),
                            new ProjectionMutationModel(
                                ProjectionMutationId::fromUuid($this->generateUuid->random()),
                                $eventProperty->id,
                                $mutation->mutationType,
                                new MutationConditionGroups(),
                            )
                        )
                            ->setProjectionProperty($projectionProperty)
                            ->setCreatedAt($now)
                    );
                }
            }

            $properties->add($projectionProperty);
        }

        $projection->setProperties($properties);

        return $projection;
    }
}

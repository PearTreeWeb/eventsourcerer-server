<?php

declare(strict_types=1);

namespace App\Domain\Projection\Handler;

use App\Domain\Projection\Command\UpdateProjection;
use App\Domain\Projection\Model\ProjectionEventProperties;
use App\Domain\Projection\Model\ProjectionEventProperty;
use App\Domain\Projection\Repository\ProjectionRepository;
use App\Entity\Projection;
use App\Entity\ProjectionProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateProjectionHandler
{
    public function __construct(
        private ProjectionRepository $projectionRepository,
        private ClockInterface $clock
    ) {}

    public function __invoke(UpdateProjection $command): void
    {
        $now = $this->clock->now();
        $projection = $this->projectionRepository->find($command->id);

        $projection
            ->setProperties(
                self::projectionProperties(
                    $command->properties,
                    $projection,
                    $now
                )
            )
            ->setIsPartitioned($command->partition)
            ->setExposeStateViaApi($command->exposeStateViaApi)
            ->setStartDate($command->startDate)
            ->setEndDate($command->endDate)
            ->setUpdatedAt($now);

        $this->projectionRepository->update($projection, $now);
    }

    /**
     * @return ArrayCollection<int, ProjectionProperty>
     */
    private static function projectionProperties(
        ProjectionEventProperties $properties,
        Projection $projection,
        \DateTimeImmutable $now
    ): ArrayCollection {
        $updatedProperties = $properties
            ->items()
            ->map(function  (ProjectionEventProperty $property) use ($now, $projection) {
                $newProperty = $projection
                    ->getProperties()
                    ->findFirst(
                        static fn (int $index, ProjectionProperty $entityProperty) => $entityProperty
                            ->id()
                            ->equals($property->id->toUuid())
                    );

                if ($newProperty) {
                    $newProperty
                        ->setType($property->type::class)
                        ->setName($property->name->toString())
                        ->setUpdatedAt($now);
                } else {
                    $newProperty = ProjectionProperty::create($property)
                        ->setProjection($projection)
                        ->setCreatedAt($now);
                }

                return $newProperty;
            })
        ->filter()
        ->values()
        ->all();

        return new ArrayCollection($updatedProperties);
    }
}

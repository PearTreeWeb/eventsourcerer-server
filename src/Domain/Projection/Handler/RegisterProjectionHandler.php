<?php

declare(strict_types=1);

namespace App\Domain\Projection\Handler;

use App\Domain\Projection\Command\RegisterProjection;
use App\Domain\Projection\Model\ProjectionEventProperties;
use App\Domain\Projection\Repository\ProjectionRepository;
use App\Entity\Projection;
use App\Entity\ProjectionProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RegisterProjectionHandler
{
    public function __construct(
        private ProjectionRepository $projectionRepository,
        private ClockInterface $clock
    ) {}

    public function __invoke(RegisterProjection $command): void
    {
        $now = $this->clock->now();

        $projection = Projection::create(
            $command->id,
            $command->name,
            $command->continuous,
            $command->partition,
            $command->exposeStateViaApi,
            $now,
            $command->startDate,
            $command->endDate,
        );

        $projection = $projection->setProperties(self::projectionProperties($command->properties, $projection, $now));

        $this->projectionRepository->create($projection);
    }

    /**
     * @return ArrayCollection<int, ProjectionProperty>
     */
    private static function projectionProperties(
        ProjectionEventProperties $properties,
        Projection $projection,
        \DateTimeImmutable $now
    ): ArrayCollection {
        /** @var ArrayCollection<int, ProjectionProperty> */
        return new ArrayCollection(
            $properties
                ->mapInto(ProjectionProperty::class)
                ->map(static fn (ProjectionProperty $entity): ProjectionProperty => $entity
                    ->setCreatedAt($now)
                    ->setProjection($projection))
                ->all()
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Extension\Packages\RecentStreams\Projections;

use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Model\EventVersion;
use App\Domain\Projection\Model\MutationBuilder;
use App\Domain\Projection\Model\ProjectionName;
use App\Domain\Projection\Model\ProjectionProperty;
use App\Domain\Projection\Model\ProjectionPropertyName;
use App\Domain\Projection\Service\ProjectionBuilderFactory;
use App\Entity\Projection as ProjectionEntity;
use App\Extension\Default\Widget\Projection;
use App\Extension\Packages\RecentStreams\Mutation\AddToRecentStreams;
use App\Extension\Packages\RecentStreams\PropertyType\RecentStreams as RecentStreamsPropertyType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.projection')]
final readonly class RecentStreams implements Projection
{
    private const string NAME = 'Recent Streams';

    private const string PROJECTION_PROPERTY_1_NAME = 'recent-streams';

    public function __construct(private ProjectionBuilderFactory $projectionBuilderFactory) {}

    public function fetch(): ProjectionEntity
    {
        $projectionProperty1Name = ProjectionPropertyName::fromString(self::PROJECTION_PROPERTY_1_NAME);

        return $this
            ->projectionBuilderFactory
            ->create(ProjectionName::fromString(self::NAME))
            ->addProperty(
                new ProjectionProperty(
                    $projectionProperty1Name,
                    RecentStreamsPropertyType::create()
                ),
            )
            ->addMutation(
                MutationBuilder::when(EventName::any(), EventVersion::fromInt(1))
                    ->then(AddToRecentStreams::create())
                    ->using(EventPropertyName::metadata())
                    ->update($projectionProperty1Name)
                    ->build()
            )
            ->build();
    }
}

<?php

declare(strict_types=1);

namespace App\Extension\Packages\RecentStreams\Mutation;

use App\Domain\Common\Model\PropertyType;
use App\Domain\Event\Model\EventPropertyValue;
use App\Domain\Projection\Model\MutationDisplayPart;
use App\Domain\Projection\Model\MutationLabel;
use App\Domain\Projection\Model\MutationPreposition;
use App\Domain\Projection\Model\SystemMutation;
use App\Extension\Packages\RecentStreams\PropertyType\RecentStreams;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.mutation_type')]
final readonly class AddToRecentStreams extends SystemMutation
{
    private const int FIXED_SIZE_ARRAY_AMOUNT = 5;

    public function __construct() {}

    public static function create(): self
    {
        return new self();
    }

    public function mutate(EventPropertyValue $eventValue, mixed $currentValue): mixed
    {
        $metadata = $eventValue->toArray();

        if (StreamId::allStream()->sameAs(StreamId::fromString($metadata['stream']))) {
            return $currentValue;
        }

        $currentValue = $currentValue ?? [];

        if (!in_array($metadata['stream'], $currentValue, true)) {
            array_unshift($currentValue, $metadata['stream']);
        }

        $firstFiveItems = array_slice($currentValue, 0, self::FIXED_SIZE_ARRAY_AMOUNT);

        return array_combine($firstFiveItems, $firstFiveItems);
    }

    public function preposition(): MutationPreposition
    {
        return MutationPreposition::USING;
    }

    public static function label(): MutationLabel
    {
        return MutationLabel::fromString('Update Recent Streams');
    }

    public static function displayOrder(): array
    {
        return [
            MutationDisplayPart::Label,
            MutationDisplayPart::ProjectionProperty,
            MutationDisplayPart::Preposition,
            MutationDisplayPart::EventProperty,
        ];
    }

    public function compatibleWith(PropertyType $propertyType): bool
    {
        return $propertyType->isSameTypeAs(RecentStreams::create());
    }
}

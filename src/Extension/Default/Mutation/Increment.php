<?php

declare(strict_types=1);

namespace App\Extension\Default\Mutation;

use App\Domain\Common\Model\PropertyType;
use App\Domain\Event\Model\EventPropertyValue;
use App\Domain\Projection\Model\MutationDisplayPart;
use App\Domain\Projection\Model\MutationLabel;
use App\Domain\Projection\Model\MutationPreposition;
use App\Domain\Projection\Model\SystemMutation;
use App\Extension\Default\PropertyType\Integer;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.mutation_type')]
final readonly class Increment extends SystemMutation
{
    private const string LABEL = 'Increment';

    private const array COMPATIBLE_PROPERTY_TYPES = [
        Integer::class,
    ];

    public static function create(): self
    {
        return new self();
    }

    public function mutate(EventPropertyValue $eventValue, mixed $currentValue): int
    {
        return (int) $currentValue +1;
    }

    public function preposition(): MutationPreposition
    {
        return MutationPreposition::NOT_APPLICABLE;
    }

    public static function label(): MutationLabel
    {
        return MutationLabel::fromString(self::LABEL);
    }

    public static function displayOrder(): array
    {
        return [
            MutationDisplayPart::Label,
            MutationDisplayPart::ProjectionProperty,
        ];
    }

    public function compatibleWith(PropertyType $propertyType): bool
    {
        return \in_array($propertyType::class, self::COMPATIBLE_PROPERTY_TYPES, true);
    }
}

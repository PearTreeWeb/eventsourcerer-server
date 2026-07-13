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
final readonly class Subtract extends SystemMutation
{
    protected const string LABEL = 'Subtract';

    private const array COMPATIBLE_PROPERTY_TYPES = [
        Integer::class,
    ];

    public function __construct() {}

    public static function create(): self
    {
        return new self();
    }

    public static function label(): MutationLabel
    {
        return MutationLabel::fromString(self::LABEL);
    }

    public function mutate(EventPropertyValue $eventValue, mixed $currentValue): mixed
    {
        return $currentValue - json_decode($eventValue->toString(), false, 512, JSON_THROW_ON_ERROR);
    }

    public function preposition(): MutationPreposition
    {
        return MutationPreposition::FROM;
    }

    public static function displayOrder(): array
    {
        return [
            MutationDisplayPart::Label,
            MutationDisplayPart::EventProperty,
            MutationDisplayPart::Preposition,
            MutationDisplayPart::ProjectionProperty,
        ];
    }

    public function compatibleWith(PropertyType $propertyType): bool
    {
        return \in_array($propertyType::class, self::COMPATIBLE_PROPERTY_TYPES, true);
    }
}

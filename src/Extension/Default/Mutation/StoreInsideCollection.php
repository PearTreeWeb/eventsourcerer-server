<?php

declare(strict_types=1);

namespace App\Extension\Default\Mutation;

use App\Domain\Common\Model\PropertyType;
use App\Domain\Event\Model\EventPropertyValue;
use App\Domain\Projection\Model\MutationDisplayPart;
use App\Domain\Projection\Model\MutationLabel;
use App\Domain\Projection\Model\MutationPreposition;
use App\Domain\Projection\Model\SystemMutation;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.mutation_type')]
final readonly class StoreInsideCollection extends SystemMutation
{
    protected const string LABEL = 'Store';

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
        $currentValue[] = $eventValue->toString();

        return $currentValue;
    }

    public function preposition(): MutationPreposition
    {
        return MutationPreposition::IN;
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
        return true;
    }
}

<?php

declare(strict_types=1);

namespace App\Extension\Packages\EventStats\Mutation;

use App\Domain\Common\Model\PropertyType;
use App\Domain\Event\Model\EventPropertyValue;
use App\Domain\Projection\Model\MutationDisplayPart;
use App\Domain\Projection\Model\MutationLabel;
use App\Domain\Projection\Model\MutationPreposition;
use App\Domain\Projection\Model\SystemMutation;
use App\Extension\Default\PropertyType\Json;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.mutation_type')]
final readonly class CountEventOccurrences extends SystemMutation
{
    private const string LABEL = 'Update Event Occurrences';

    public function __construct() {}

    public static function create(): self
    {
        return new self();
    }

    public function mutate(EventPropertyValue $eventValue, mixed $currentValue): mixed
    {
        $currentValue = $currentValue?: [];

        /** @var array{event: string, version: int} $decoded */
        $decoded = json_decode($eventValue->toString(), true);

        $index = sprintf(
            '%s-v%d',
            $decoded['event'],
            $decoded['version']
        );

        if (!isset($currentValue[$index])) {
            $currentValue[$index] = 0;
        }

        $currentValue[$index]++;

        return $currentValue;
    }

    public function preposition(): MutationPreposition
    {
        return MutationPreposition::USING;
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
            MutationDisplayPart::Preposition,
            MutationDisplayPart::EventProperty
        ];
    }

    public function compatibleWith(PropertyType $propertyType): bool
    {
        return $propertyType->isSameTypeAs(Json::create());
    }
}

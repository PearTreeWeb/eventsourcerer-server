<?php

declare(strict_types=1);

namespace App\Extension\Packages\Geo\Mutation;

use App\Domain\Common\Model\Author;
use App\Domain\Common\Model\AuthorUniqueIdentifier;
use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\Packages;
use App\Domain\Common\Model\PropertyType;
use App\Domain\Event\Model\EventPropertyValue;
use App\Domain\Projection\Model\MutationDisplayPart;
use App\Domain\Projection\Model\MutationLabel;
use App\Domain\Projection\Model\MutationPreposition;
use App\Domain\Projection\Model\SystemMutation;
use App\Extension\Packages\Geo\PropertyType\LatLongs;
use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.mutation_type')]
final readonly class AddToLatLongs extends SystemMutation
{
    private const string CATEGORY = 'Geo';
    private const string LABEL = 'Add Lat Long';

    public function __construct(private ClockInterface $clock) {}

    public function mutate(EventPropertyValue $eventValue, mixed $currentValue): mixed
    {
        if (null === $currentValue) {
            $currentValue = [];
        }

        $currentValue[$eventValue->toString()] = $this->clock->now()->getTimestamp();

        return $currentValue;
    }

    public function preposition(): MutationPreposition
    {
        return MutationPreposition::TO;
    }

    public function uniqueIdentifier(): AuthorUniqueIdentifier
    {
        return AuthorUniqueIdentifier::fromString(
            sprintf('%s-%s', self::author(), self::label())
        );
    }

    public static function label(): MutationLabel
    {
        return MutationLabel::fromString(self::LABEL);
    }

    public static function displayOrder(): array
    {
        return [
            MutationDisplayPart::Label,
            MutationDisplayPart::Preposition,
            MutationDisplayPart::ProjectionProperty,
        ];
    }

    public function compatibleWith(PropertyType $propertyType): bool
    {
        return $propertyType->isSameTypeAs(LatLongs::create());
    }

    public static function author(): Author
    {
        return Author::fromString(
            sprintf(
                '%s - %s',
                Author::eventSourcerer(),
                self::CATEGORY
            )
        );
    }

    public static function package(): Package
    {
        return Package::fromString(Packages::Geo->value);
    }
}

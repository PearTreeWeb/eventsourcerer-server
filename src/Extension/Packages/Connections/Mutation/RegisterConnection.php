<?php

declare(strict_types=1);

namespace App\Extension\Packages\Connections\Mutation;

use App\Domain\Common\Model\Author;
use App\Domain\Common\Model\AuthorUniqueIdentifier;
use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\Packages;
use App\Domain\Common\Model\PropertyType;
use App\Domain\Event\Model\EventPropertyValue;
use App\Domain\Projection\Model\CanMutate;
use App\Domain\Projection\Model\MutationDisplayPart;
use App\Domain\Projection\Model\MutationLabel;
use App\Domain\Projection\Model\MutationPreposition;
use App\Extension\Packages\RecentStreams\PropertyType\RecentStreams;
use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.mutation_type')]
final readonly class RegisterConnection implements CanMutate
{
    private const string CATEGORY = 'Networking';
    private const string LABEL = 'Add connection to';

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
        return MutationPreposition::USING;
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
            MutationDisplayPart::ProjectionProperty,
            MutationDisplayPart::Preposition,
            MutationDisplayPart::EventProperty,
        ];
    }

    public function compatibleWith(PropertyType $propertyType): bool
    {
        return $propertyType->isSameTypeAs(RecentStreams::create());
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
        return Package::fromString(Packages::Network->value);
    }
}

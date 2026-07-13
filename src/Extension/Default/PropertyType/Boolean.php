<?php

declare(strict_types=1);

namespace App\Extension\Default\PropertyType;

use App\Domain\Common\Exception\PropertyTypeValueIsIncompatible;
use App\Domain\Common\Model\Author;
use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\Packages;
use App\Domain\Common\Model\PropertyType;
use App\Domain\Common\Model\PropertyTypeDescription;
use App\Extension\Default\ConditionOperators\EqualTo;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.property_type')]
final readonly class Boolean implements PropertyType
{
    use PropertyTypeComparison;

    private const string DESCRIPTION = 'Boolean';

    public function __construct() {}

    public static function create(): self
    {
        return new self();
    }

    public static function serialize(mixed $value): string
    {
        return (string) $value;
    }

    public static function deserialize(string $value): bool
    {
        return 'true' === $value;
    }

    public static function name(): PropertyTypeDescription
    {
        return PropertyTypeDescription::fromString(self::DESCRIPTION);
    }

    public static function author(): Author
    {
        return Author::eventSourcerer();
    }

    public static function validate(mixed $value): void
    {
        if (!is_bool($value)) {
            throw PropertyTypeValueIsIncompatible::with((string) $value, self::DESCRIPTION);
        }
    }

    public static function conditionOperators(): array
    {
        return [
            EqualTo::class,
        ];
    }

    public static function package(): Package
    {
        return Package::fromString(Packages::Base->value);
    }

    public static function toString(string $value): string
    {
        return $value;
    }

    public function canBeUsedWithProjectionPropertyType(PropertyType $propertyType): bool
    {
        return $this->isSameTypeAs($propertyType);
    }

    public static function exampleInput(): string
    {
        return 'true';
    }
}

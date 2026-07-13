<?php

declare(strict_types=1);

namespace App\Extension\Packages\Geo\PropertyType;

use App\Domain\Common\Exception\PropertyTypeValueIsIncompatible;
use App\Domain\Common\Model\Author;
use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\Packages;
use App\Domain\Common\Model\PropertyType;
use App\Domain\Common\Model\PropertyTypeDescription;
use App\Extension\Default\ConditionOperators\EqualTo;
use App\Extension\Default\ConditionOperators\NotEqualTo;
use App\Extension\Default\ConditionOperators\GreaterThan;
use App\Extension\Default\ConditionOperators\LessThan;
use App\Extension\Default\ConditionOperators\NumericEqualTo;
use App\Extension\Default\ConditionOperators\NumericGreaterThan;
use App\Extension\Default\ConditionOperators\NumericLessThan;
use App\Extension\Default\ConditionOperators\NumericNotEqualTo;
use App\Extension\Default\PropertyType\PropertyTypeComparison;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.property_type')]
final readonly class Long implements PropertyType
{
    use PropertyTypeComparison;

    private const string DESCRIPTION = 'Longitude';

    public static function author(): Author
    {
        return Author::eventSourcerer();
    }

    public static function create(): PropertyType
    {
        return new self();
    }

    public static function name(): PropertyTypeDescription
    {
        return PropertyTypeDescription::fromString(self::DESCRIPTION);
    }

    public static function serialize(mixed $value): string
    {
        return (string) $value;
    }

    public static function deserialize(string $value): mixed
    {
        return (float) $value;
    }

    public static function validate(mixed $value): void
    {
        if (is_numeric($value) && $value >= -180 && $value <= 180) {
            return;
        }

        throw PropertyTypeValueIsIncompatible::with((string) $value, self::DESCRIPTION);
    }

    public static function conditionOperators(): array
    {
        return [
            NumericGreaterThan::class,
            NumericLessThan::class,
            NumericEqualTo::class,
            NumericNotEqualTo::class,
        ];
    }

    public static function package(): Package
    {
        return Package::fromString(Packages::Geo->value);
    }

    public static function toString(string $value): string
    {
        return $value;
    }

    public static function exampleInput(): string
    {
        return '0.1278';
    }
}

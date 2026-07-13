<?php

declare(strict_types=1);

namespace App\Extension\Packages\Connections\PropertyType;

use App\Domain\Common\Model\Author;
use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\Packages;
use App\Domain\Common\Model\PropertyType;
use App\Domain\Common\Model\PropertyTypeDescription;
use App\Extension\Default\ConditionOperators\ContainsText;
use App\Extension\Default\ConditionOperators\DoesNotContainText;
use App\Extension\Default\ConditionOperators\EqualTo;
use App\Extension\Default\ConditionOperators\Excludes;
use App\Extension\Default\ConditionOperators\Includes;
use App\Extension\Default\ConditionOperators\NotEqualTo;
use App\Extension\Default\PropertyType\PropertyTypeComparison;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.property_type')]
final readonly class IPAddress implements PropertyType
{
    use PropertyTypeComparison;

    private const string DESCRIPTION = 'IP Address';

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
        return $value;
    }

    public static function deserialize(string $value): mixed
    {
        return $value;
    }

    public static function validate(mixed $value): void
    {
//        if (false === filter_var($value, FILTER_VALIDATE_IP)) {
//            throw PropertyTypeValueIsIncompatible::with((string) $value, self::DESCRIPTION);
//        }
    }

    public static function conditionOperators(): array
    {
        return [
            ContainsText::class,
            DoesNotContainText::class,
            EqualTo::class,
            NotEqualTo::class,
            Excludes::class,
            Includes::class,
            Excludes::class,
        ];
    }

    public static function package(): Package
    {
        return Package::fromString(Packages::Network->value);
    }

    public static function toString(string $value): string
    {
        return $value;
    }

    public static function exampleInput(): string
    {
        return '192.168.1.1';
    }
}

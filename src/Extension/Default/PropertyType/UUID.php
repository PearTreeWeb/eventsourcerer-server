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
final readonly class UUID implements PropertyType
{
    use PropertyTypeComparison;

    private const string DESCRIPTION = 'UUID';

    private const string UUID_REGEX_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    public function __construct() {}

    public static function create(): PropertyType
    {
        return new self();
    }

    public static function author(): Author
    {
        return Author::eventSourcerer();
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
        if (false === preg_match(self::UUID_REGEX_PATTERN, $value)) {
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

    public static function exampleInput(): string
    {
        return '4e9f4d37-910f-4a59-b552-13f094ed279c';
    }
}

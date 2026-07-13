<?php

declare(strict_types=1);

namespace App\Extension\Default\PropertyType;

use App\Domain\Common\Exception\PropertyTypeValueIsIncompatible;
use App\Domain\Common\Model\Author;
use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\Packages;
use App\Domain\Common\Model\PropertyType;
use App\Domain\Common\Model\PropertyTypeDescription;
use App\Extension\Default\ConditionOperators\ContainsText;
use App\Extension\Default\ConditionOperators\DoesNotContainText;
use App\Extension\Default\ConditionOperators\EqualTo;
use App\Extension\Default\ConditionOperators\NotEqualTo;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.property_type')]
final readonly class Text implements PropertyType
{
    use PropertyTypeComparison;

    private const string DESCRIPTION = 'Text';

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

    public static function deserialize(string $value): string
    {
        return $value;
    }

    public static function validate(mixed $value): void
    {
        if (!is_string($value)) {
            throw PropertyTypeValueIsIncompatible::with((string) $value, self::DESCRIPTION);
        }
    }

    public static function conditionOperators(): array
    {
        return [
            EqualTo::class,
            NotEqualTo::class,
            ContainsText::class,
            DoesNotContainText::class,
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
        return 'Hello World';
    }
}

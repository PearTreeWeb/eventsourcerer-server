<?php

declare(strict_types=1);

namespace App\Domain\Common\Model;

use App\Domain\Common\Exception\ExpectedDateTimeImmutable;
use App\Domain\Common\Exception\InvalidDateTimeFormat;
use App\Extension\Default\ConditionOperators\Excludes;
use App\Extension\Default\ConditionOperators\Includes;
use App\Extension\Default\PropertyType\PropertyTypeComparison;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.property_type')]
final class DateTimeType implements PropertyType
{
    use PropertyTypeComparison;

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
        return PropertyTypeDescription::fromString('Datetime-UTC');
    }

    /**
     * @param \DateTimeImmutable $value
     */
    public static function serialize(mixed $value): string
    {
        return (string) $value->getTimestamp();
    }

    public static function deserialize(string $value): \DateTimeImmutable
    {
        if (!is_numeric($value)) {
            throw InvalidDateTimeFormat::with($value);
        }

        return \DateTimeImmutable::createFromTimestamp((int) $value);
    }

    public static function toString(string $value): string
    {
        try {
            $value = self::deserialize($value);
        } catch (InvalidDateTimeFormat) {
            return $value;
        }

        return $value->format('Y-m-d H:i:s');
    }

    public static function validate(mixed $value): void
    {
        if (!($value instanceof \DateTimeImmutable)) {
            throw ExpectedDateTimeImmutable::butReceived((string) $value);
        }
    }

    public static function conditionOperators(): array
    {
        return [
            Includes::class,
            Excludes::class,
        ];
    }

    public static function package(): Package
    {
        return Package::fromString(Packages::Base->value);
    }

    public static function exampleInput(): string
    {
        return '1781500657';
    }
}

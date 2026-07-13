<?php

declare(strict_types=1);

namespace App\Extension\Default\PropertyType;

use App\Domain\Common\Exception\PropertyTypeValueIsIncompatible;
use App\Domain\Common\Model\Author;
use App\Domain\Common\Model\PropertyType;
use App\Domain\Common\Model\PropertyTypeDescription;
use App\Extension\Default\ConditionOperators\EqualTo;
use App\Extension\Default\ConditionOperators\Excludes;
use App\Extension\Default\ConditionOperators\Includes;
use App\Extension\Default\ConditionOperators\NotEqualTo;

abstract class SimpleArray implements PropertyType
{
    use PropertyTypeComparison;

    protected const string DESCRIPTION = '';

    public function __construct() {}

    public static function author(): Author
    {
        return Author::eventSourcerer();
    }

    public static function create(): static
    {
        /** @phpstan-ignore new.static */
        return new static();
    }

    public static function name(): PropertyTypeDescription
    {
        return PropertyTypeDescription::fromString(static::DESCRIPTION);
    }

    public static function serialize(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public static function deserialize(string $value): array
    {
        return json_decode($value, false, 512, JSON_THROW_ON_ERROR);
    }

    public static function validate(mixed $value): void
    {
        if (!is_array($value)) {
            throw PropertyTypeValueIsIncompatible::with((string) $value, static::DESCRIPTION);
        }
    }

    public static function conditionOperators(): array
    {
        return [
            EqualTo::class,
            NotEqualTo::class,
            Includes::class,
            Excludes::class,
        ];
    }

    public static function toString(string $value): string
    {
        return $value;
    }
}

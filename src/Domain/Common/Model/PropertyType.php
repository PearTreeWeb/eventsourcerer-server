<?php

namespace App\Domain\Common\Model;

interface PropertyType extends HasAuthor
{
    public static function create(): self;

    public static function name(): PropertyTypeDescription;

    public static function serialize(mixed $value): string;

    public static function deserialize(string $value): mixed;

    public function isSameTypeAs(self $other): bool;

    public static function validate(mixed $value): void;

    /**
     * @return array<class-string>
     */
    public static function conditionOperators(): array;

    public static function package(): Package;

    public static function toString(string $value): string;

    public static function exampleInput(): string;

    public function canBeUsedWithProjectionPropertyType(self $propertyType): bool;
}

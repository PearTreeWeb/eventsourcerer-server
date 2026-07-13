<?php

namespace App\Extension\Packages\Geo\PropertyType;

use App\Domain\Common\Exception\PropertyTypeValueIsIncompatible;
use App\Domain\Common\Model\Author;
use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\Packages;
use App\Domain\Common\Model\PropertyType;
use App\Domain\Common\Model\PropertyTypeDescription;
use App\Extension\Default\PropertyType\PropertyTypeComparison;
use App\Extension\Packages\Geo\ConditionOperators\WithinRadius;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.property_type')]
final readonly class LatAndLong implements PropertyType
{
    use PropertyTypeComparison;

    private const string DESCRIPTION = 'Lat and Long';

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

    /**
     * @param array{longitude: float, latitude: float} $value
     */
    public static function serialize(mixed $value): string
    {
        return json_encode($value);
    }

    public static function deserialize(string $value): mixed
    {
        $longAndLat = json_decode($value, true);

        return [
            'longitude' => $longAndLat['longitude'],
            'latitude' => $longAndLat['latitude'],
        ];
    }

    public static function validate(mixed $value): void
    {
       $parts = explode(',', $value);

       try {
           Lat::validate($parts[0]);
           Long::validate($parts[1]);
       } catch (PropertyTypeValueIsIncompatible) {
           throw PropertyTypeValueIsIncompatible::with($value, self::DESCRIPTION);
       }
    }

    public static function conditionOperators(): array
    {
        return [
            WithinRadius::class,
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
        return '{"latitude": 51.5074, "longitude": 0.1278}';
    }

    public function canBeUsedWithProjectionPropertyType(PropertyType $propertyType): bool
    {
        return $this->isSameTypeAs($propertyType);
    }
}

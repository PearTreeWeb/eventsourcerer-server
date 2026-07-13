<?php

declare(strict_types=1);

namespace App\Extension\Packages\Geo\PropertyType;

use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\Packages;
use App\Domain\Common\Model\PropertyType;
use App\Extension\Default\PropertyType\SimpleArray;
use App\Extension\Packages\Geo\ConditionOperators\WithinRadius;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.property_type')]
final class LatLongs extends SimpleArray
{
    protected const string DESCRIPTION = 'Lat and Longs';

    public static function exampleInput(): string
    {
        return '[
            \'44.968046, -94.420307\', 
            \'44.33328, -89.132008\',
        ]';
    }

    public static function package(): Package
    {
        return Package::fromString(Packages::Geo->value);
    }

    public function canBeUsedWithProjectionPropertyType(PropertyType $propertyType): bool
    {
        return $propertyType->isSameTypeAs(LatAndLong::create());
    }

    public static function conditionOperators(): array
    {
        return [
            WithinRadius::class,
        ];
    }
}

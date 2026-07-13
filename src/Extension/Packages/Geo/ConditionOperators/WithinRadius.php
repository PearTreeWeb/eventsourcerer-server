<?php

namespace App\Extension\Packages\Geo\ConditionOperators;

use App\Domain\Common\Model\AuthoredBySystem;
use App\Domain\Common\Model\AuthorUniqueIdentifier;
use App\Domain\Common\Model\FulfilCanProvidePackageUniqueIdentifier;
use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\Packages;
use App\Domain\Projection\Model\ConditionLabel;
use App\Extension\Default\ConditionOperators\ConditionOperator;
use App\Extension\Default\PropertyType\Numeric;
use App\Extension\Packages\Geo\PropertyType\LatAndLong;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @template T
 */
#[AutoconfigureTag('eventsourcerer.condition_operator')]
final readonly class WithinRadius implements ConditionOperator
{
    use AuthoredBySystem;
    use FulfilCanProvidePackageUniqueIdentifier;

    public function uniqueIdentifier(): AuthorUniqueIdentifier
    {
        return self::uniquePackageIdentifier(self::author(), static::label());
    }

    public static function label(): ConditionLabel
    {
        return ConditionLabel::fromString('Within radius of');
    }

    public function compute(mixed $value, mixed $parameter): bool
    {
        if (!is_array($value) || !isset($value['latitude'], $value['longitude'])) {
            return false;
        }

        if (!is_array($parameter) || count($parameter) < 2) {
            return false;
        }

        $radius = (float) $parameter[0];
        $target = $parameter[1];

        if (is_string($target)) {
            $parts = explode(',', $target);
            if (count($parts) === 2) {
                $target = [
                    'latitude' => trim($parts[0]),
                    'longitude' => trim($parts[1]),
                ];
            }
        }

        if (!is_array($target) || !isset($target['latitude'], $target['longitude'])) {
            return false;
        }

        $distance = $this->haversineDistance(
            (float) $value['latitude'],
            (float) $value['longitude'],
            (float) $target['latitude'],
            (float) $target['longitude']
        );

        return $distance <= $radius;
    }

    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public static function package(): Package
    {
        return Package::fromString(Packages::Geo->value);
    }

    public static function parameters(): array
    {
        return ['Radius (km)', 'Coordinate'];
    }

    public static function parameterPropertyTypes(): array
    {
        return [Numeric::class, LatAndLong::class];
    }
}

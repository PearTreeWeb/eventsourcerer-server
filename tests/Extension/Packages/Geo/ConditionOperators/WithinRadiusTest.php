<?php

declare(strict_types=1);

namespace App\Tests\Extension\Packages\Geo\ConditionOperators;

use App\Extension\Packages\Geo\ConditionOperators\WithinRadius;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class WithinRadiusTest extends TestCase
{
    #[DataProvider('withinRadiusProvider')]
    public function testWithinRadius(array $value, array $parameter, bool $expected): void
    {
        $operator = new WithinRadius();
        $this->assertEquals($expected, $operator->compute($value, $parameter));
    }

    public static function withinRadiusProvider(): array
    {
        return [
            // London to London (0km radius)
            [
                ['latitude' => 51.5074, 'longitude' => 0.1278],
                [10, ['latitude' => 51.5074, 'longitude' => 0.1278]],
                true
            ],
            // London to Watford (~39km) within 50km
            [
                ['latitude' => 51.5074, 'longitude' => 0.1278],
                [50, ['latitude' => 51.6565, 'longitude' => -0.3903]],
                true
            ],
            // London to Watford (~39km) within 50km with string coordinate
            [
                ['latitude' => 51.5074, 'longitude' => 0.1278],
                [50, '51.6565, -0.3903'],
                true
            ],
            // London to Watford (~39km) NOT within 20km
            [
                ['latitude' => 51.5074, 'longitude' => 0.1278],
                [20, ['latitude' => 51.6565, 'longitude' => -0.3903]],
                false
            ],
            // New York to London (far away)
            [
                ['latitude' => 40.7128, 'longitude' => -74.0060],
                [100, ['latitude' => 51.5074, 'longitude' => 0.1278]],
                false
            ],
        ];
    }
}

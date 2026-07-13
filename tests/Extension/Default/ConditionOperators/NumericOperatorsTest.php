<?php

declare(strict_types=1);

namespace App\Tests\Extension\Default\ConditionOperators;

use App\Extension\Default\ConditionOperators\NumericEqualTo;
use App\Extension\Default\ConditionOperators\NumericGreaterThan;
use App\Extension\Default\ConditionOperators\NumericLessThan;
use App\Extension\Default\ConditionOperators\NumericNotEqualTo;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NumericOperatorsTest extends TestCase
{
    #[DataProvider('numericGreaterThanProvider')]
    public function testNumericGreaterThan(string $value, string $parameter, bool $expected): void
    {
        $operator = new NumericGreaterThan();
        $this->assertEquals($expected, $operator->compute($value, $parameter));
    }

    public static function numericGreaterThanProvider(): array
    {
        return [
            ['10', '5', true],
            ['5', '10', false],
            ['10', '10', false],
            ['10.5', '10.4', true],
            ['10.4', '10.5', false],
            ['10.000', '10', false],
        ];
    }

    #[DataProvider('numericLessThanProvider')]
    public function testNumericLessThan(string $value, string $parameter, bool $expected): void
    {
        $operator = new NumericLessThan();
        $this->assertEquals($expected, $operator->compute($value, $parameter));
    }

    public static function numericLessThanProvider(): array
    {
        return [
            ['5', '10', true],
            ['10', '5', false],
            ['10', '10', false],
            ['10.4', '10.5', true],
            ['10.5', '10.4', false],
            ['10.000', '10', false],
        ];
    }

    #[DataProvider('numericEqualToProvider')]
    public function testNumericEqualTo(string $value, string $parameter, bool $expected): void
    {
        $operator = new NumericEqualTo();
        $this->assertEquals($expected, $operator->compute($value, $parameter));
    }

    public static function numericEqualToProvider(): array
    {
        return [
            ['10', '10', true],
            ['10', '5', false],
            ['10.0', '10', true],
            ['10.000', '10.0', true],
            ['10.0001', '10', false],
        ];
    }

    #[DataProvider('numericNotEqualToProvider')]
    public function testNumericNotEqualTo(string $value, string $parameter, bool $expected): void
    {
        $operator = new NumericNotEqualTo();
        $this->assertEquals($expected, $operator->compute($value, $parameter));
    }

    public static function numericNotEqualToProvider(): array
    {
        return [
            ['10', '5', true],
            ['10', '10', false],
            ['10.0', '10', false],
            ['10.1', '10', true],
        ];
    }
}

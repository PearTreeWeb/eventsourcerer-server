<?php

declare(strict_types=1);

namespace App\Extension\Default\ConditionOperators;

use App\Domain\Projection\Model\ConditionLabel;
use App\Domain\Projection\Model\SystemConditionOperator;
use App\Extension\Default\PropertyType\Numeric;
use BcMath\Number;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @template T
 */
#[AutoconfigureTag('eventsourcerer.condition_operator')]
final readonly class NumericNotEqualTo extends SystemConditionOperator
{
    /**
     * @param T $value
     * @param T $parameter
     */
    public function compute(mixed $value, mixed $parameter): bool
    {
        return new Number($value)
            ->compare(new Number($parameter)) !== 0;
    }

    public static function label(): ConditionLabel
    {
        return ConditionLabel::fromString('Numeric not equal to');
    }

    public static function parameterPropertyTypes(): array
    {
        return [Numeric::class];
    }
}

<?php

declare(strict_types=1);

namespace App\Extension\Default\ConditionOperators;

use App\Domain\Projection\Model\ConditionLabel;
use App\Domain\Projection\Model\SystemConditionOperator;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @template T
 */
#[AutoconfigureTag('eventsourcerer.condition_operator')]
final readonly class NotEqualToCaseInsensitive extends SystemConditionOperator
{
    /**
     * @param T $value
     * @param T $parameter
     */
    public function compute(mixed $value, mixed $parameter): bool
    {
        return mb_strtolower($value) !== mb_strtolower($parameter);
    }

    public static function label(): ConditionLabel
    {
        return ConditionLabel::fromString('Not equal to (case insensitive)');
    }
}

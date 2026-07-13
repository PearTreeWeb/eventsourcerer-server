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
final readonly class LessThan extends SystemConditionOperator
{
    /**
     * @param T $value
     * @param T $parameter
     */
    public function compute(mixed $value, mixed $parameter): bool
    {
        return $value < $parameter;
    }

    public static function label(): ConditionLabel
    {
        return ConditionLabel::fromString('Less than');
    }
}

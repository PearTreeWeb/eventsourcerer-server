<?php

declare(strict_types=1);

namespace App\Extension\Default\ConditionOperators;

use App\Domain\Projection\Model\ConditionLabel;
use App\Domain\Projection\Model\SystemConditionOperator;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.condition_operator')]
final readonly class InsideProjectionCollection extends SystemConditionOperator
{
    /**
     * @param mixed $value The event property value
     * @param mixed $parameter The projection property name (string) or the collection itself if pre-processed
     */
    public function compute(mixed $value, mixed $parameter): bool
    {
        if (!is_array($parameter)) {
             // If it's not an array, it might be the property name that didn't get resolved or an empty state
             // But in the new ConditionsChecker logic, we should pass the resolved array here.
             return false;
        }

        return in_array($value, $parameter, true);
    }

    public static function label(): ConditionLabel
    {
        return ConditionLabel::fromString('is inside projection collection');
    }

    public static function parameters(): array
    {
        return ['Projection Property'];
    }
}

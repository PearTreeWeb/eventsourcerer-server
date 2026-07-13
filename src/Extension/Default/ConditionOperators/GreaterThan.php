<?php

declare(strict_types=1);

namespace App\Extension\Default\ConditionOperators;

use App\Domain\Projection\Model\ConditionLabel;
use App\Domain\Projection\Model\SystemConditionOperator;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.condition_operator')]
final readonly class GreaterThan extends SystemConditionOperator
{
    public function compute(mixed $value, mixed $parameter): bool
    {
        return $value > $parameter;
    }

    public static function label(): ConditionLabel
    {
        return ConditionLabel::fromString('Greater than');
    }
}

<?php

declare(strict_types=1);

namespace App\Extension\Default\ConditionOperators;

use App\Domain\Projection\Model\ConditionLabel;
use App\Domain\Projection\Model\SystemConditionOperator;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.condition_operator')]
final readonly class ContainsTextCaseInsensitive extends SystemConditionOperator
{
    /**
     * @param string $value
     * @param string $parameter
     */
    public function compute(mixed $value, mixed $parameter): bool
    {
        return str_contains(mb_strtolower($value),  mb_strtolower($parameter));
    }

    public static function label(): ConditionLabel
    {
        return ConditionLabel::fromString('Contains text (case insensitive)');
    }
}

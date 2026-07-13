<?php

declare(strict_types=1);

namespace App\Extension\Default\ConditionOperators;

use App\Domain\Common\Exception\CannotComputeConditionOperatorLogic;
use App\Domain\Projection\Model\ConditionLabel;
use App\Domain\Projection\Model\SystemConditionOperator;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @template T
 */
#[AutoconfigureTag('eventsourcerer.condition_operator')]
final readonly class Excludes extends SystemConditionOperator
{
    /**
     * @param T $value
     * @param T $parameter
     */
    public function compute(mixed $value, mixed $parameter): bool
    {
        if (!is_array($parameter)) {
            throw CannotComputeConditionOperatorLogic::becauseOfUnexpectedParameterType('array', gettype($parameter));
        }

        return !\in_array($value, $parameter, true);
    }

    public static function label(): ConditionLabel
    {
        return ConditionLabel::fromString('Excludes');
    }
}

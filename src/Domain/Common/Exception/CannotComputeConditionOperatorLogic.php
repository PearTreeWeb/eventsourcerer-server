<?php

declare(strict_types=1);

namespace App\Domain\Common\Exception;

final class CannotComputeConditionOperatorLogic extends \RuntimeException
{
    public static function becauseOfUnexpectedParameterType(string $expected, string $received): self
    {
        return new self(
            sprintf(
                'Cannot compute condition operator logic because expected parameter type was "%s" and received "%s"',
                $expected,
                $received
            )
        );
    }
}

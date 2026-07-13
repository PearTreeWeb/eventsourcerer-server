<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class StartDateBeforeEndDate extends Constraint
{
    public string $message = 'The end date cannot be before the start date.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}

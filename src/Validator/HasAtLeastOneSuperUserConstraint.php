<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

final class HasAtLeastOneSuperUserConstraint extends Constraint
{
    public string $message = 'Cannot change role, doing so would result in no super users';
    public string $mode    = 'strict';
}

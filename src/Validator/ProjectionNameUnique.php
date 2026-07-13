<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
final class ProjectionNameUnique extends Constraint
{
    public string $message = 'A projection with this name already exists.';
}

<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
final class LowercaseHyphenatedFormat extends Constraint
{
    public string $message = 'This value must be lowercase and hyphenated, e.g. "my-event-name".';
}

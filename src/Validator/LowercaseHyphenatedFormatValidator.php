<?php

declare(strict_types=1);

namespace App\Validator;

use PearTreeWeb\EventSourcerer\Common\Model\IsString;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class LowercaseHyphenatedFormatValidator extends ConstraintValidator
{
    private const string FORMAT_PATTERN = '/^[a-z0-9]+(-[a-z0-9]+)*$/';

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof LowercaseHyphenatedFormat) {
            throw new UnexpectedTypeException($constraint, LowercaseHyphenatedFormat::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof IsString) {
            throw new UnexpectedValueException($value, IsString::class);
        }

        if (1 !== preg_match(self::FORMAT_PATTERN, $value->toString())) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Validator;

use App\Domain\Projection\Command\RegisterProjection;
use App\Domain\Projection\Command\UpdateProjection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class StartDateBeforeEndDateValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof StartDateBeforeEndDate) {
            throw new UnexpectedTypeException($constraint, StartDateBeforeEndDate::class);
        }

        if (!$value instanceof RegisterProjection && !$value instanceof UpdateProjection) {
            return;
        }

        if (null === $value->startDate || null === $value->endDate) {
            return;
        }

        if ($value->endDate < $value->startDate) {
            $this->context->buildViolation($constraint->message)
                ->atPath('endDate')
                ->addViolation();
        }
    }
}

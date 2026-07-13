<?php

declare(strict_types=1);

namespace App\Validator;

use App\Domain\Projection\Model\ProjectionName;
use App\Domain\Projection\Repository\ProjectionRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class ProjectionNameUniqueValidator extends ConstraintValidator
{
    public function __construct(private readonly ProjectionRepository $projectionRepository) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ProjectionNameUnique) {
            throw new UnexpectedTypeException($constraint, ProjectionNameUnique::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof ProjectionName) {
            throw new UnexpectedValueException($value, ProjectionName::class);
        }

        if (!$this->projectionRepository->doesNotExist($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}

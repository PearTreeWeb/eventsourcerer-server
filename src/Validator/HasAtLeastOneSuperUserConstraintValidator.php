<?php

declare(strict_types=1);

namespace App\Validator;

use App\Domain\User\Model\Role;
use App\Domain\User\Repository\UserRepository;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class HasAtLeastOneSuperUserConstraintValidator extends ConstraintValidator
{
    public function __construct(private readonly UserRepository $userRepository) {}

    /**
     * @param Role $value
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof HasAtLeastOneSuperUserConstraint) {
            throw new UnexpectedTypeException($constraint, HasAtLeastOneSuperUserConstraint::class);
        }

        if (Role::SUPER_USER === $value) {
            return;
        }

        if ($this->userRepository->hasOnlyOneSuperUser()) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}

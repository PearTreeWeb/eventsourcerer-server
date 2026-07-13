<?php

declare(strict_types=1);

namespace App\Tests\Validator;

use App\Domain\Projection\Model\ProjectionName;
use App\Domain\Projection\Repository\ProjectionRepository;
use App\Validator\ProjectionNameUnique;
use App\Validator\ProjectionNameUniqueValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class ProjectionNameUniqueValidatorTest extends TestCase
{
    public function testItDoesNotAddViolationIfNameDoesNotExist(): void
    {
        $projectionRepository = $this->createMock(ProjectionRepository::class);
        $context = $this->createMock(ExecutionContextInterface::class);

        $name = ProjectionName::fromString('New Projection');

        $projectionRepository->expects($this->once())
            ->method('doesNotExist')
            ->with($name)
            ->willReturn(true);

        $context->expects($this->never())
            ->method('buildViolation');

        $validator = new ProjectionNameUniqueValidator($projectionRepository);
        $validator->initialize($context);

        $validator->validate($name, new ProjectionNameUnique());
    }

    public function testItAddsViolationIfNameAlreadyExists(): void
    {
        $projectionRepository = $this->createMock(ProjectionRepository::class);
        $context = $this->createMock(ExecutionContextInterface::class);
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $name = ProjectionName::fromString('Existing Projection');
        $constraint = new ProjectionNameUnique();

        $projectionRepository->expects($this->once())
            ->method('doesNotExist')
            ->with($name)
            ->willReturn(false);

        $context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($violationBuilder);

        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $validator = new ProjectionNameUniqueValidator($projectionRepository);
        $validator->initialize($context);

        $validator->validate($name, $constraint);
    }

    public function testItSkipsValidationIfValueIsEmpty(): void
    {
        $projectionRepository = $this->createMock(ProjectionRepository::class);
        $context = $this->createMock(ExecutionContextInterface::class);

        $projectionRepository->expects($this->never())
            ->method('doesNotExist');

        $validator = new ProjectionNameUniqueValidator($projectionRepository);
        $validator->initialize($context);

        $validator->validate(null, new ProjectionNameUnique());
        $validator->validate('', new ProjectionNameUnique());
    }
}

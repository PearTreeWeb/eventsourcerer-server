<?php

declare(strict_types=1);

namespace App\Tests\Validator;

use App\Domain\Projection\Command\RegisterProjection;
use App\Domain\Projection\Model\ProjectionEventProperties;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionName;
use App\Validator\StartDateBeforeEndDate;
use App\Validator\StartDateBeforeEndDateValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class StartDateBeforeEndDateValidatorTest extends TestCase
{
    public function testItDoesNotAddViolationIfDatesAreValid(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->never())->method('buildViolation');

        $command = new RegisterProjection(
            ProjectionId::fromUuid(\Symfony\Component\Uid\Uuid::v4()),
            ProjectionName::fromString('Test'),
            ProjectionEventProperties::fromArray([]),
            false,
            false,
            false,
            new \DateTimeImmutable('2023-01-01'),
            new \DateTimeImmutable('2023-01-02')
        );

        $validator = new StartDateBeforeEndDateValidator();
        $validator->initialize($context);
        $validator->validate($command, new StartDateBeforeEndDate());
    }

    public function testItAddsViolationIfEndDateIsBeforeStartDate(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new StartDateBeforeEndDate();
        
        $context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($violationBuilder);

        $violationBuilder->expects($this->once())
            ->method('atPath')
            ->with('endDate')
            ->willReturn($violationBuilder);

        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $command = new RegisterProjection(
            ProjectionId::fromUuid(\Symfony\Component\Uid\Uuid::v4()),
            ProjectionName::fromString('Test'),
            ProjectionEventProperties::fromArray([]),
            false,
            false,
            false,
            new \DateTimeImmutable('2023-01-02'),
            new \DateTimeImmutable('2023-01-01')
        );

        $validator = new StartDateBeforeEndDateValidator();
        $validator->initialize($context);
        $validator->validate($command, $constraint);
    }

    public function testItSkipsValidationIfDatesAreNull(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->never())->method('buildViolation');

        $command = new RegisterProjection(
            ProjectionId::fromUuid(\Symfony\Component\Uid\Uuid::v4()),
            ProjectionName::fromString('Test'),
            ProjectionEventProperties::fromArray([]),
            false,
            false,
            false,
            null,
            null
        );

        $validator = new StartDateBeforeEndDateValidator();
        $validator->initialize($context);
        $validator->validate($command, new StartDateBeforeEndDate());
    }
}

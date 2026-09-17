<?php

declare(strict_types=1);

namespace App\Tests\Validator;

use App\Domain\Event\Model\EventName;
use App\Validator\LowercaseHyphenatedFormat;
use App\Validator\LowercaseHyphenatedFormatValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class LowercaseHyphenatedFormatValidatorTest extends TestCase
{
    /**
     * @dataProvider validNameProvider
     */
    public function testItDoesNotAddViolationForValidNames(string $value): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);

        $context->expects($this->never())
            ->method('buildViolation');

        $validator = new LowercaseHyphenatedFormatValidator();
        $validator->initialize($context);

        $validator->validate(EventName::fromString($value), new LowercaseHyphenatedFormat());
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function validNameProvider(): iterable
    {
        yield 'single word' => ['event'];
        yield 'hyphenated' => ['user-signed-up'];
        yield 'with numbers' => ['item-dispatched-2'];
    }

    /**
     * @dataProvider invalidNameProvider
     */
    public function testItAddsViolationForInvalidNames(string $value): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $constraint = new LowercaseHyphenatedFormat();

        $context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($violationBuilder);

        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $validator = new LowercaseHyphenatedFormatValidator();
        $validator->initialize($context);

        $validator->validate(EventName::fromString($value), $constraint);
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function invalidNameProvider(): iterable
    {
        yield 'contains uppercase' => ['User-Signed-Up'];
        yield 'contains spaces' => ['user signed up'];
        yield 'contains underscore' => ['user_signed_up'];
        yield 'leading hyphen' => ['-user-signed-up'];
        yield 'trailing hyphen' => ['user-signed-up-'];
        yield 'double hyphen' => ['user--signed-up'];
    }

    public function testItSkipsValidationIfValueIsEmpty(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);

        $context->expects($this->never())
            ->method('buildViolation');

        $validator = new LowercaseHyphenatedFormatValidator();
        $validator->initialize($context);

        $validator->validate(null, new LowercaseHyphenatedFormat());
        $validator->validate('', new LowercaseHyphenatedFormat());
    }
}

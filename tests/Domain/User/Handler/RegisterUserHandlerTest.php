<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\Handler;

use App\Domain\User\Command\RegisterUser;
use App\Domain\User\Handler\RegisterUserHandler;
use App\Tests\Double\Entity;
use App\Tests\Double\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

final class RegisterUserHandlerTest extends TestCase
{
    private MockClock $clock;

    public function setUp(): void
    {
        $this->clock = new MockClock();
    }

    public function testItRegistersUser(): void
    {
        $resetPasswordHelperMock = $this->createMock(ResetPasswordHelperInterface::class);
        $mailerMock              = $this->createMock(MailerInterface::class);

        $mailerMock->expects($this->once())->method('send');

        $resetPasswordHelperMock
            ->method('generateResetToken')
            ->willReturn(
                new ResetPasswordToken('test-token', $this->clock->now()),
            );

        $handler = new RegisterUserHandler(
            new UserRepository(),
            $this->clock,
            $resetPasswordHelperMock,
            $mailerMock
        );

        $handler(
            new RegisterUser(Entity::userSuper())
        );
    }
}

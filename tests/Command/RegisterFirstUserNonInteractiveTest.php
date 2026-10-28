<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\RegisterFirstUserNonInteractive;
use App\Domain\Author\Repository\AuthorRepository;
use App\Domain\User\Model\Role;
use App\Domain\User\Repository\UserRepository;
use App\Entity\Author;
use App\Entity\User;
use App\Tests\Double\Entity;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

final class RegisterFirstUserNonInteractiveTest extends TestCase
{
    private MockClock $clock;

    protected function setUp(): void
    {
        $this->clock = new MockClock('2026-10-27 12:00:00');
    }

    public function testItFailsIfUserAlreadyExists(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('all')->willReturn([Entity::user()]);

        $authorRepository = $this->createMock(AuthorRepository::class);
        $authorRepository->expects($this->never())->method('create');

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $resetPasswordHelper = $this->createMock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->expects($this->never())->method('generateResetToken');

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->never())->method('send');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $command = new RegisterFirstUserNonInteractive(
            $userRepository,
            $authorRepository,
            $passwordHasher,
            $resetPasswordHelper,
            $mailer,
            $urlGenerator,
            $this->clock,
        );

        $tester = new CommandTester($command);
        $exitCode = $tester->execute([
            '--forename' => 'Alice',
            '--surname' => 'Smith',
            '--email' => 'alice@example.com',
        ]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('A user already exists', $tester->getDisplay());
    }

    public function testItFailsWhenRequiredInputsAreMissing(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('all')->willReturn([]);

        $authorRepository = $this->createMock(AuthorRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $resetPasswordHelper = $this->createMock(ResetPasswordHelperInterface::class);
        $mailer = $this->createMock(MailerInterface::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $command = new RegisterFirstUserNonInteractive(
            $userRepository,
            $authorRepository,
            $passwordHasher,
            $resetPasswordHelper,
            $mailer,
            $urlGenerator,
            $this->clock,
        );

        $tester = new CommandTester($command);

        // Missing forename
        $exitCode = $tester->execute([
            '--surname' => 'Smith',
            '--email' => 'alice@example.com',
        ], ['interactive' => false]);
        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Forename is required.', $tester->getDisplay());

        // Missing surname
        $exitCode = $tester->execute([
            '--forename' => 'Alice',
            '--email' => 'alice@example.com',
        ], ['interactive' => false]);
        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Surname is required.', $tester->getDisplay());

        // Missing email
        $exitCode = $tester->execute([
            '--forename' => 'Alice',
            '--surname' => 'Smith',
        ], ['interactive' => false]);
        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('A valid email address is required.', $tester->getDisplay());

        // Invalid email
        $exitCode = $tester->execute([
            '--forename' => 'Alice',
            '--surname' => 'Smith',
            '--email' => 'not-an-email',
        ], ['interactive' => false]);
        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('A valid email address is required.', $tester->getDisplay());
    }

    public function testItSuccessfullyRegistersFirstUserWithOptions(): void
    {
        $createdUser = null;
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('all')->willReturn([]);
        $userRepository->expects($this->once())
            ->method('create')
            ->willReturnCallback(function (User $user) use (&$createdUser) {
                $createdUser = $user;
                return $user;
            });

        $createdAuthor = null;
        $authorRepository = $this->createMock(AuthorRepository::class);
        $authorRepository->expects($this->once())->method('findByName')->with('Acme Corp')->willReturn(null);
        $authorRepository->expects($this->once())
            ->method('create')
            ->willReturnCallback(function (Author $author) use (&$createdAuthor) {
                $createdAuthor = $author;
                return $author;
            });

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hashed_dummy_password');

        $resetToken = new ResetPasswordToken('dummy-reset-token', $this->clock->now());
        $resetPasswordHelper = $this->createMock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->expects($this->once())
            ->method('generateResetToken')
            ->willReturn($resetToken);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->once())
            ->method('generate')
            ->with(
                'app_reset_password',
                ['token' => 'dummy-reset-token'],
                UrlGeneratorInterface::ABSOLUTE_URL,
            )
            ->willReturn('https://eventsourcerer.docker.localhost/password/reset/dummy-reset-token');

        $sentEmail = null;
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())
            ->method('send')
            ->willReturnCallback(function ($email) use (&$sentEmail) {
                $sentEmail = $email;
            });

        $command = new RegisterFirstUserNonInteractive(
            $userRepository,
            $authorRepository,
            $passwordHasher,
            $resetPasswordHelper,
            $mailer,
            $urlGenerator,
            $this->clock,
        );

        $tester = new CommandTester($command);
        $exitCode = $tester->execute([
            '--forename' => 'Alice',
            '--surname' => 'Smith',
            '--email' => 'alice@example.com',
            '--company' => 'Acme Corp',
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $display = $tester->getDisplay();
        $this->assertStringContainsString('First super user "alice@example.com" registered successfully.', $display);
        $this->assertStringContainsString('https://eventsourcerer.docker.localhost/password/reset/dummy-reset-token', $display);

        // Verify User properties
        $this->assertNotNull($createdUser);
        $this->assertSame('alice@example.com', $createdUser->getEmail());
        $this->assertSame('Alice', $createdUser->getForename());
        $this->assertSame('Smith', $createdUser->getSurname());
        $this->assertSame('Acme Corp', $createdUser->getCompanyName());
        $this->assertContains(Role::SUPER_USER->value, $createdUser->getRoles());
        $this->assertFalse($createdUser->isVerified());
        $this->assertSame('hashed_dummy_password', $createdUser->getPassword());

        // Verify Author properties
        $this->assertNotNull($createdAuthor);
        $this->assertSame('Acme Corp', $createdAuthor->getName());

        // Verify Email properties
        $this->assertInstanceOf(TemplatedEmail::class, $sentEmail);
        $this->assertSame('Please Confirm your Email', $sentEmail->getSubject());
        $this->assertCount(1, $sentEmail->getTo());
        $this->assertSame('alice@example.com', $sentEmail->getTo()[0]->getAddress());
        $this->assertSame('UI/user/confirmation_email.html.twig', $sentEmail->getHtmlTemplate());
        $context = $sentEmail->getContext();
        $this->assertSame('dummy-reset-token', $context['token']);
        $this->assertSame('https://eventsourcerer.docker.localhost/password/reset/dummy-reset-token', $context['resetUrl']);
    }

    public function testItSuccessfullyRegistersFirstUserWithArguments(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('all')->willReturn([]);
        $userRepository->expects($this->once())->method('create')->willReturnArgument(0);

        $createdAuthor = null;
        $authorRepository = $this->createMock(AuthorRepository::class);
        $authorRepository->expects($this->once())->method('findByName')->with('Bob Jones')->willReturn(null);
        $authorRepository->expects($this->once())
            ->method('create')
            ->willReturnCallback(function (Author $author) use (&$createdAuthor) {
                $createdAuthor = $author;
                return $author;
            });

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hashed');

        $resetToken = new ResetPasswordToken('bob-token', $this->clock->now());
        $resetPasswordHelper = $this->createMock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->method('generateResetToken')->willReturn($resetToken);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('https://example.com/password/reset/bob-token');

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())->method('send');

        $command = new RegisterFirstUserNonInteractive(
            $userRepository,
            $authorRepository,
            $passwordHasher,
            $resetPasswordHelper,
            $mailer,
            $urlGenerator,
            $this->clock,
        );

        $tester = new CommandTester($command);
        $exitCode = $tester->execute([
            'forename' => 'Bob',
            'surname' => 'Jones',
            'email' => 'bob@example.com',
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertNotNull($createdAuthor);
        $this->assertSame('Bob Jones', $createdAuthor->getName());
    }

    public function testItSuccessfullyRegistersFirstUserWithEnvironmentVariables(): void
    {
        $_ENV['FIRST_USER_FORENAME'] = 'Charlie';
        $_ENV['FIRST_USER_SURNAME'] = 'Brown';
        $_ENV['FIRST_USER_EMAIL'] = 'charlie@example.com';
        $_ENV['FIRST_USER_COMPANY'] = 'Brown Enterprises';

        try {
            $userRepository = $this->createMock(UserRepository::class);
            $userRepository->method('all')->willReturn([]);
            $userRepository->expects($this->once())->method('create')->willReturnArgument(0);

            $authorRepository = $this->createMock(AuthorRepository::class);
            $authorRepository->expects($this->once())->method('findByName')->with('Brown Enterprises')->willReturn(null);
            $authorRepository->expects($this->once())->method('create')->willReturnArgument(0);

            $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
            $passwordHasher->method('hashPassword')->willReturn('hashed');

            $resetToken = new ResetPasswordToken('charlie-token', $this->clock->now());
            $resetPasswordHelper = $this->createMock(ResetPasswordHelperInterface::class);
            $resetPasswordHelper->method('generateResetToken')->willReturn($resetToken);

            $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
            $urlGenerator->method('generate')->willReturn('https://example.com/reset/charlie-token');

            $mailer = $this->createMock(MailerInterface::class);
            $mailer->expects($this->once())->method('send');

            $command = new RegisterFirstUserNonInteractive(
                $userRepository,
                $authorRepository,
                $passwordHasher,
                $resetPasswordHelper,
                $mailer,
                $urlGenerator,
                $this->clock,
            );

            $tester = new CommandTester($command);
            $exitCode = $tester->execute([], ['interactive' => false]);

            $this->assertSame(Command::SUCCESS, $exitCode);
            $this->assertStringContainsString('First super user "charlie@example.com" registered successfully.', $tester->getDisplay());
        } finally {
            unset(
                $_ENV['FIRST_USER_FORENAME'],
                $_ENV['FIRST_USER_SURNAME'],
                $_ENV['FIRST_USER_EMAIL'],
                $_ENV['FIRST_USER_COMPANY'],
            );
        }
    }

    public function testItHandlesResetPasswordTokenGenerationFailure(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('all')->willReturn([]);
        $userRepository->expects($this->once())->method('create')->willReturnArgument(0);

        $authorRepository = $this->createMock(AuthorRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $exception = new class extends \Exception implements ResetPasswordExceptionInterface {
            public function getReason(): string
            {
                return 'Token generation failed';
            }
        };

        $resetPasswordHelper = $this->createMock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->method('generateResetToken')->willThrowException($exception);

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->never())->method('send');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $command = new RegisterFirstUserNonInteractive(
            $userRepository,
            $authorRepository,
            $passwordHasher,
            $resetPasswordHelper,
            $mailer,
            $urlGenerator,
            $this->clock,
        );

        $tester = new CommandTester($command);
        $exitCode = $tester->execute([
            '--forename' => 'Alice',
            '--surname' => 'Smith',
            '--email' => 'alice@example.com',
        ]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Failed to generate reset password token: Token generation failed', $tester->getDisplay());
    }

    public function testItHandlesMailerExceptionGracefully(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('all')->willReturn([]);
        $userRepository->expects($this->once())->method('create')->willReturnArgument(0);

        $authorRepository = $this->createMock(AuthorRepository::class);
        $authorRepository->method('findByName')->willReturn(null);
        $authorRepository->expects($this->once())->method('create')->willReturnArgument(0);

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $resetToken = new ResetPasswordToken('token-123', $this->clock->now());
        $resetPasswordHelper = $this->createMock(ResetPasswordHelperInterface::class);
        $resetPasswordHelper->method('generateResetToken')->willReturn($resetToken);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('https://example.com/reset/token-123');

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->method('send')->willThrowException(new TransportException('Connection refused'));

        $command = new RegisterFirstUserNonInteractive(
            $userRepository,
            $authorRepository,
            $passwordHasher,
            $resetPasswordHelper,
            $mailer,
            $urlGenerator,
            $this->clock,
        );

        $tester = new CommandTester($command);
        $exitCode = $tester->execute([
            '--forename' => 'Alice',
            '--surname' => 'Smith',
            '--email' => 'alice@example.com',
        ]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $display = $tester->getDisplay();
        $this->assertStringContainsString('Could not send email to "alice@example.com": Connection refused', $display);
        $this->assertStringContainsString('One-time password setup link:', $display);
        $this->assertStringContainsString('https://example.com/reset/token-123', $display);
    }
}

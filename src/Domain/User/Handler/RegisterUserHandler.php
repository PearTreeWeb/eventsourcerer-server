<?php

declare(strict_types=1);

namespace App\Domain\User\Handler;

use App\Domain\User\Command\RegisterUser;
use App\Domain\User\Repository\UserRepository;
use Psr\Clock\ClockInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[AsMessageHandler]
final readonly class RegisterUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private ClockInterface $clock,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private MailerInterface $mailer
    ) {}

    public function __invoke(RegisterUser $command): void
    {
        $user               = $this->userRepository->create($command->user, $this->clock->now());
        $resetPasswordToken = $this->resetPasswordHelper->generateResetToken($command->user);

        $email = (new TemplatedEmail())
            ->from(new Address('info@eventsourcerer.com', 'Info - EventSourcerer'))
            ->to($user->getEmail())
            ->subject('Please Confirm your Email')
            ->context(['token' => $resetPasswordToken->getToken()])
            ->htmlTemplate('UI/user/confirmation_email.html.twig');

        $this->mailer->send($email);
    }
}

<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\User\Model\EmailAddress;
use App\Domain\User\Model\Role;
use App\Domain\User\Model\UserId;
use App\Domain\User\Repository\UserRepository;
use App\Entity\User;
use Psr\Clock\ClockInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: self::COMMAND,
    description: 'Register the first super user (one-time bootstrap command).',
)]
final class RegisterFirstUser extends Command
{
    public const string COMMAND = 'app:user:register-first';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ClockInterface $clock,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (count($this->userRepository->all()) > 0) {
            $io->error(
                'A user already exists. This command can only be used to register the very first user. '
                . 'Use the web UI to manage users from now on.'
            );

            return Command::FAILURE;
        }

        $io->title('Register the first super user');

        $emailQuestion = new Question('Email address: ');
        $emailQuestion->setValidator(static function (?string $value): string {
            $value = trim((string) $value);
            if ($value === '' || filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                throw new \RuntimeException('A valid email address is required.');
            }

            return $value;
        });
        $email = (string) $io->askQuestion($emailQuestion);

        $passwordQuestion = new Question('Password: ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);
        $passwordQuestion->setValidator(static function (?string $value): string {
            $value = (string) $value;
            if (strlen($value) < 8) {
                throw new \RuntimeException('Password must be at least 8 characters long.');
            }

            return $value;
        });
        $password = (string) $io->askQuestion($passwordQuestion);

        $confirmQuestion = new Question('Confirm password: ');
        $confirmQuestion->setHidden(true);
        $confirmQuestion->setHiddenFallback(false);
        $confirm = (string) $io->askQuestion($confirmQuestion);

        if ($password !== $confirm) {
            $io->error('Passwords do not match.');

            return Command::FAILURE;
        }

        // Re-check inside transactional moment to avoid races.
        if (count($this->userRepository->all()) > 0) { // @phpstan-ignore greater.alwaysFalse
            $io->error('A user already exists. Aborting.');

            return Command::FAILURE;
        }

        $user = User::create(
            UserId::fromUuid(Uuid::v7()),
            EmailAddress::fromString($email),
            Role::SUPER_USER,
        );
        $user->setAsSuperUser();
        $user->setIsVerified(true);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->userRepository->create($user, $this->clock->now());

        $io->success(sprintf('First super user "%s" created successfully.', $email));

        return Command::SUCCESS;
    }
}

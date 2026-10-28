<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\Author\Model\AuthorId;
use App\Domain\Author\Repository\AuthorRepository;
use App\Domain\User\Model\EmailAddress;
use App\Domain\User\Model\Role;
use App\Domain\User\Model\UserId;
use App\Domain\User\Repository\UserRepository;
use App\Entity\Author;
use App\Entity\User;
use Psr\Clock\ClockInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[AsCommand(
    name: self::COMMAND,
    description: 'Register the first super user non-interactively (ECS task) and email a one-time password setup link.',
    aliases: ['app:user:register-first:non-interactive', 'app:user:register-first-ecs'],
)]
final class RegisterFirstUserNonInteractive extends Command
{
    public const string COMMAND = 'app:user:register-first-non-interactive';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AuthorRepository $authorRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ClockInterface $clock,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('forename', null, InputOption::VALUE_REQUIRED, 'Forename of the super user')
            ->addOption('surname', null, InputOption::VALUE_REQUIRED, 'Surname of the super user')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email address of the super user')
            ->addOption('company', null, InputOption::VALUE_REQUIRED, 'Company name (optional)')
            ->addOption('company-name', null, InputOption::VALUE_REQUIRED, 'Company name (alias for --company)')
            ->addArgument('forename', InputArgument::OPTIONAL, 'Forename of the super user')
            ->addArgument('surname', InputArgument::OPTIONAL, 'Surname of the super user')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email address of the super user')
            ->addArgument('company', InputArgument::OPTIONAL, 'Company name (optional)');
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

        $forename = $this->resolveValue(
            $input,
            'forename',
            ['FIRST_USER_FORENAME', 'SUPERUSER_FORENAME'],
        );

        if ($forename === null && $input->isInteractive()) {
            $question = new Question('Forename: ');
            $question->setValidator(static function (?string $value): string {
                $value = trim((string) $value);
                if ($value === '') {
                    throw new \RuntimeException('Forename is required.');
                }

                return $value;
            });
            $forename = (string) $io->askQuestion($question);
        }

        if ($forename === null || $forename === '') {
            $io->error('Forename is required.');

            return Command::FAILURE;
        }

        $surname = $this->resolveValue(
            $input,
            'surname',
            ['FIRST_USER_SURNAME', 'SUPERUSER_SURNAME'],
        );

        if ($surname === null && $input->isInteractive()) {
            $question = new Question('Surname: ');
            $question->setValidator(static function (?string $value): string {
                $value = trim((string) $value);
                if ($value === '') {
                    throw new \RuntimeException('Surname is required.');
                }

                return $value;
            });
            $surname = (string) $io->askQuestion($question);
        }

        if ($surname === null || $surname === '') {
            $io->error('Surname is required.');

            return Command::FAILURE;
        }

        $email = $this->resolveValue(
            $input,
            'email',
            ['FIRST_USER_EMAIL', 'SUPERUSER_EMAIL'],
        );

        if ($email === null && $input->isInteractive()) {
            $question = new Question('Email address: ');
            $question->setValidator(static function (?string $value): string {
                $value = trim((string) $value);
                if ($value === '' || filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                    throw new \RuntimeException('A valid email address is required.');
                }

                return $value;
            });
            $email = (string) $io->askQuestion($question);
        }

        if ($email === null || $email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $io->error('A valid email address is required.');

            return Command::FAILURE;
        }

        $companyName = $this->resolveValue(
            $input,
            'company',
            ['FIRST_USER_COMPANY', 'FIRST_USER_COMPANY_NAME', 'SUPERUSER_COMPANY'],
            'company-name',
        );

        if ($companyName === null && $input->isInteractive()) {
            $question = new Question('Company name (optional): ', '');
            $companyName = trim((string) $io->askQuestion($question));
            $companyName = $companyName === '' ? null : $companyName;
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
            $forename,
            $surname,
            $companyName,
        );
        $user->setAsSuperUser();
        $user->setIsVerified(false);

        $temporaryPassword = bin2hex(random_bytes(32));
        $user->setPassword($this->passwordHasher->hashPassword($user, $temporaryPassword));

        $this->userRepository->create($user, $this->clock->now());

        try {
            $resetPasswordToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            $io->error(sprintf('Failed to generate reset password token: %s', $e->getReason()));

            return Command::FAILURE;
        }

        $resetUrl = $this->urlGenerator->generate(
            'app_reset_password',
            ['token' => $resetPasswordToken->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $emailMessage = (new TemplatedEmail())
            ->from(new Address('info@eventsourcerer.com', 'Info - EventSourcerer'))
            ->to($user->getEmail())
            ->subject('Please Confirm your Email')
            ->context([
                'token' => $resetPasswordToken->getToken(),
                'resetUrl' => $resetUrl,
            ])
            ->htmlTemplate('UI/user/confirmation_email.html.twig');

        try {
            $this->mailer->send($emailMessage);
            $io->info(sprintf('A one-time password setup link has been emailed to "%s".', $email));
        } catch (\Throwable $e) {
            $io->warning(sprintf('Could not send email to "%s": %s', $email, $e->getMessage()));
        }

        $io->writeln(sprintf('<info>One-time password setup link:</info> %s', $resetUrl));

        $authorName = $companyName ?? sprintf('%s %s', $forename, $surname);
        if ($this->authorRepository->findByName($authorName) === null) {
            $author = Author::create(AuthorId::fromUuid(Uuid::v7()), $authorName);
            $this->authorRepository->create($author);
            $io->writeln(sprintf('<info>Author "%s" created.</info>', $authorName));
        }

        $io->success(sprintf('First super user "%s" registered successfully.', $email));

        return Command::SUCCESS;
    }

    /**
     * @param string[] $envKeys
     */
    private function resolveValue(
        InputInterface $input,
        string $name,
        array $envKeys,
        ?string $aliasOption = null,
    ): ?string {
        if ($input->hasOption($name)) {
            $optionVal = $input->getOption($name);
            if ($optionVal !== null && trim((string) $optionVal) !== '') {
                return trim((string) $optionVal);
            }
        }

        if ($aliasOption !== null && $input->hasOption($aliasOption)) {
            $aliasVal = $input->getOption($aliasOption);
            if ($aliasVal !== null && trim((string) $aliasVal) !== '') {
                return trim((string) $aliasVal);
            }
        }

        if ($input->hasArgument($name)) {
            $argVal = $input->getArgument($name);
            if ($argVal !== null && trim((string) $argVal) !== '') {
                return trim((string) $argVal);
            }
        }

        foreach ($envKeys as $envKey) {
            $envVal = $_ENV[$envKey] ?? getenv($envKey);
            if ($envVal !== false && trim((string) $envVal) !== '') {
                return trim((string) $envVal);
            }
        }

        return null;
    }
}

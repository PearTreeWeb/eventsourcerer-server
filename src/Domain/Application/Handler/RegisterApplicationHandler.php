<?php

declare(strict_types=1);

namespace App\Domain\Application\Handler;

use App\Domain\Application\Command\RegisterApplication;
use App\Domain\Application\Repository\ApplicationRepository;
use App\Domain\Application\Service\CertificateGenerator;
use App\Entity\Application;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
final readonly class RegisterApplicationHandler
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private CertificateGenerator $certificateGenerator,
        private ClockInterface $clock,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function __invoke(RegisterApplication $command): string
    {
        $application = Application::create(
            $command->id,
            $command->name,
            $this->clock->now(),
            $command->hostname,
        );

        $secret = bin2hex(random_bytes(32));
        $application->setSecret($this->passwordHasher->hashPassword($application, $secret));

        $this->certificateGenerator->generateForApplication($application);
        $this->applicationRepository->create($application);

        return $secret;
    }
}

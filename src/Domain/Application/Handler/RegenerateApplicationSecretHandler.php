<?php

declare(strict_types=1);

namespace App\Domain\Application\Handler;

use App\Domain\Application\Command\RegenerateApplicationSecret;
use App\Domain\Application\Repository\ApplicationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
final readonly class RegenerateApplicationSecretHandler
{
    public function __construct(
        private ApplicationRepository $applicationRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function __invoke(RegenerateApplicationSecret $command): string
    {
        $application = $this->applicationRepository->byIdStrict($command->id);

        $secret = bin2hex(random_bytes(32));
        $application->setSecret($this->passwordHasher->hashPassword($application, $secret));

        $this->applicationRepository->update($application);

        return $secret;
    }
}

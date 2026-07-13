<?php

declare(strict_types=1);

namespace App\Domain\Application\Handler;

use App\Domain\Application\Command\EditApplication;
use App\Domain\Application\Repository\ApplicationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class EditApplicationHandler
{
    public function __construct(private ApplicationRepository $applicationRepository) {}

    public function __invoke(EditApplication $command): void
    {
        $application = $this->applicationRepository->byIdStrict($command->id);

        $this->applicationRepository->update(
            $application
                ->setName($command->name)
                ->setHostname($command->hostname)
        );
    }
}

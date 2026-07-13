<?php

declare(strict_types=1);

namespace App\Domain\Settings\Handler;

use App\Domain\Settings\Command\UpdateSettings;
use App\Domain\Settings\Repository\SettingsRepository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateSettingsHandler
{
    public function __construct(
        private SettingsRepository $settingsRepository,
        private ClockInterface $clock,
    ) {}

    public function __invoke(UpdateSettings $command): void
    {
        $now = $this->clock->now();

        $settings = $this
            ->settingsRepository
            ->getOrCreate($now)
            ->setPublicSshKey($command->publicSshKey, $now);

        $this->settingsRepository->update($settings);
    }
}

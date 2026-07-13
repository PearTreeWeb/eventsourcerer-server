<?php

declare(strict_types=1);

namespace App\Domain\Settings\Handler;

use App\Domain\Settings\Query\GetSettings;
use App\Domain\Settings\Repository\SettingsRepository;
use App\Entity\Settings;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetSettingsHandler
{
    public function __construct(
        private SettingsRepository $settingsRepository,
    ) {}

    public function __invoke(GetSettings $query): ?Settings
    {
        return $this->settingsRepository->get();
    }
}

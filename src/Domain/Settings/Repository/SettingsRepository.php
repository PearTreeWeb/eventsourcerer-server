<?php

declare(strict_types=1);

namespace App\Domain\Settings\Repository;

use App\Entity\Settings;

interface SettingsRepository
{
    public function get(): ?Settings;

    public function getOrCreate(\DateTimeImmutable $now): Settings;

    public function update(Settings $settings): Settings;
}

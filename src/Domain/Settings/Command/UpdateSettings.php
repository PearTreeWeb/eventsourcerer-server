<?php

declare(strict_types=1);

namespace App\Domain\Settings\Command;

use App\Domain\Settings\Model\PublicSshKey;

final readonly class UpdateSettings
{
    public function __construct(public PublicSshKey $publicSshKey) {}
}

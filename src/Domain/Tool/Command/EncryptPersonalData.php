<?php

declare(strict_types=1);

namespace App\Domain\Tool\Command;

final readonly class EncryptPersonalData
{
    public function __construct(public \DateTimeImmutable $beforeDate) {}
}

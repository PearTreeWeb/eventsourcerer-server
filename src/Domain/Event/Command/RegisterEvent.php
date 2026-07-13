<?php

declare(strict_types=1);

namespace App\Domain\Event\Command;

use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventProperties;

final readonly class RegisterEvent
{
    public function __construct(
        public EventId $id,
        public EventName $name,
        public EventProperties $properties,
        public int $tombstoneAfter
    ) {}
}

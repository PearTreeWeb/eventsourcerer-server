<?php

declare(strict_types=1);

namespace App\Domain\Projection\Command;

final readonly class DeleteCondition
{
    public function __construct(public int $conditionId) {}
}

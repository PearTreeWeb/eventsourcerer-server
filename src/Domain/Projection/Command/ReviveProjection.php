<?php

namespace App\Domain\Projection\Command;

use App\Domain\Projection\Model\ProjectionId;

final readonly class ReviveProjection
{
    public function __construct(public ProjectionId $id) {}
}
<?php

declare(strict_types=1);

namespace App\Domain\Projection\Command;

use App\Domain\Projection\Model\ProjectionId;

final readonly class DeleteProjection
{
    public function __construct(public ProjectionId $id) {}
}

<?php

declare(strict_types=1);

namespace App\Domain\Projection\Query;

use App\Domain\Common\Model\Query;
use App\Domain\Projection\Model\ProjectionId;

/**
 * @implements Query<int>
 */
final readonly class GetProjectionMaxSequence implements Query
{
    public function __construct(public ProjectionId $id) {}
}

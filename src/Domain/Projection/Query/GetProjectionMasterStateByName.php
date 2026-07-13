<?php

declare(strict_types=1);

namespace App\Domain\Projection\Query;

use App\Domain\Common\Model\Query;
use App\Domain\Projection\Model\ProjectionName;
use App\Entity\ProjectionState;

/**
 * @implements Query<?ProjectionState>
 */
final readonly class GetProjectionMasterStateByName implements Query
{
    public function __construct(public ProjectionName $projectionName) {}
}

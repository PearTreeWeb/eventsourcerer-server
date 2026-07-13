<?php

declare(strict_types=1);

namespace App\Domain\Projection\Query;

use App\Domain\Common\Model\Query;
use App\Domain\Projection\Model\ProjectionId;
use App\Entity\ProjectionState;

/**
 * @implements Query<?ProjectionState>
 */
final readonly class GetProjectionMasterStateById implements Query
{
    public function __construct(public ProjectionId $id)
    {
    }
}

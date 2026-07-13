<?php

namespace App\Domain\Projection\Query;

use App\Domain\Common\Model\Query;
use App\Domain\Projection\Model\ProjectionMutationId;
use App\Entity\ProjectionMutation;

/**
 * @implements Query<ProjectionMutation>
 */
final readonly class GetProjectionMutation implements Query
{
    public function __construct(public ProjectionMutationId $id) {}
}

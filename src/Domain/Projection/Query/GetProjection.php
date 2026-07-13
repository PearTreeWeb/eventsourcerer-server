<?php

declare(strict_types=1);

namespace App\Domain\Projection\Query;

use App\Domain\Common\Model\Query;
use App\Domain\Projection\Model\ProjectionId;
use App\Entity\Projection;

/**
 * @implements Query<Projection>
 */
final readonly class GetProjection implements Query
{
    private function __construct(public ProjectionId $id) {}

    public static function withId(ProjectionId $id): self
    {
        return new self($id);
    }
}

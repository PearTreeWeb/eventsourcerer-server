<?php

namespace App\Domain\Projection\Service;

use App\Entity\ProjectionMutation;
use Illuminate\Support\Collection;

final readonly class ProjectionHelper
{
    /**
     * @param ProjectionMutation[] $mutations
     *
     * @return array<string, Collection<int, ProjectionMutation>>
     */
    public static function keyMutationsByEvent(array $mutations): array
    {
        /** @var array<string, Collection<int, ProjectionMutation>> */
        return collect($mutations)
            ->groupBy(static fn (ProjectionMutation $mutation) => $mutation->getEventId()->toString())
            ->all();
    }
}

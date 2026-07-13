<?php

namespace App\Domain\Projection\Model;

use App\Domain\Common\Model\FulfilIsCollection;
use App\Domain\Common\Model\IsCollection;

/**
 * @implements IsCollection<string, ProjectionMutationConditionGroup>
 */
final class ProjectionMutationConditionGroups implements IsCollection
{
    /**
     * @use FulfilIsCollection<string, ProjectionMutationConditionGroup>
     */
    use FulfilIsCollection;
}
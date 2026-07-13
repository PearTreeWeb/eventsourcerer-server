<?php

namespace App\Domain\Projection\Model;

use App\Domain\Common\HasKey;
use App\Domain\Common\Model\FulfilIsCollection;
use App\Domain\Common\Model\IsCollection;

/**
 * @implements IsCollection<string, ProjectionMutationCondition>
 */
final class ProjectionMutationConditionGroup implements IsCollection, HasKey
{
    /**
     * @use FulfilIsCollection<string, ProjectionMutationCondition>
     */
    use FulfilIsCollection;

    private ConditionGroupAndOr $groupType;

    public function withGroupType(ConditionGroupAndOr $groupType): static
    {
        $clone = clone $this;
        $clone->groupType = $groupType;

        return $clone;
    }

    public function groupType(): ConditionGroupAndOr
    {
        return $this->groupType ?? ConditionGroupAndOr::And;
    }

    public function key(): string
    {
        return $this->items()->first()?->key();
    }
}
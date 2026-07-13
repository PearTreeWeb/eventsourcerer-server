<?php

namespace App\Domain\Projection\Model;

use App\Domain\Common\HasKey;

final readonly class ProjectionMutationCondition implements HasKey
{
    public function __construct(
        public ProjectionMutationConditionGroupKey $key,
        public MutationType $mutationType,
        public ConditionParameterValues $parameterValues,
    ) {}

    public function key(): string
    {
        return $this->key->toString();
    }
}

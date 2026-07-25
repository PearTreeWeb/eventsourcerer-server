<?php

namespace App\Domain\Projection\Model;

use App\Domain\Common\HasKey;

final readonly class ProjectionMutationCondition implements HasKey
{
    public function __construct(
        public ProjectionMutationConditionGroupKey $key,
        public MutationType $mutationType,
        public ConditionParameterValues $parameterValues,
        public ?string $eventPropertyId = null,
        public ?string $eventPropertyType = null,
    ) {}

    public function key(): string
    {
        return $this->key->toString();
    }
}

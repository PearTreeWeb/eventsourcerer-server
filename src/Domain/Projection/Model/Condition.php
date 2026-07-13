<?php

declare(strict_types=1);

namespace App\Domain\Projection\Model;

use App\Domain\Common\HasKey;
use App\Domain\Common\Model\AuthorUniqueIdentifier;

final readonly class Condition implements HasKey
{
    public function __construct(
        public AuthorUniqueIdentifier $uniqueIdentifier,
        public ConditionParameterValues $parameterValues,
    ) {}

    public function key(): string
    {
        return sprintf('%s-%s', $this->uniqueIdentifier, $this->parameterValues->items()->implode('-'));
    }
}

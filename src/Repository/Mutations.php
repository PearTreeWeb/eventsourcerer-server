<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\Common\Model\AuthorUniqueIdentifier;
use App\Domain\Projection\Model\CanMutate;
use App\Domain\Projection\Model\MutationType;

final readonly class Mutations
{
    /**
     * @param iterable<CanMutate> $mutationTypes
     */
    public function __construct(private iterable $mutationTypes) {}

    /**
     * @return iterable<CanMutate>
     */
    public function all(): iterable
    {
        return $this->mutationTypes;
    }

    public function byType(MutationType $type): ?CanMutate
    {
        return collect($this->mutationTypes)
            ->first(static fn (CanMutate $mutation): bool => $mutation->uniqueIdentifier()->toString() === $type->toString());
    }
}

<?php

namespace App\Domain\Projection\Model;

use App\Entity\MutationConditionsGroup;
use Illuminate\Support\Collection;

final readonly class MutationConditionGroups
{
    /**
     * @var Collection<int, MutationConditionsGroup>
     */
    private Collection $items;

    public function __construct()
    {
        $this->items = new Collection();
    }

    public function add(MutationConditionsGroup $mutationConditionsGroup): void
    {
        $this->items->add($mutationConditionsGroup);
    }

    /**
     * @return Collection<int, MutationConditionsGroup>
     */
    public function items(): Collection
    {
        return $this->items;
    }
}

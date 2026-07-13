<?php

namespace App\Entity;

use App\Domain\Projection\Model\ConditionGroupAndOr;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MutationConditionsGroup
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'id')]
    private int $id;

    #[ORM\Column(type: Types::STRING)]
    private string $groupType;

    /** @var Collection<int, MutationCondition> $conditionsGroup */
    #[ORM\OneToMany(targetEntity: MutationCondition::class, mappedBy: 'conditionsGroup', cascade: ['persist'])]
    private Collection $conditionsGroup;

    #[ORM\ManyToOne(targetEntity: ProjectionMutation::class, inversedBy: 'projectionMutation')]
    private ?ProjectionMutation $projectionMutation = null;

    /**
     * @param array<MutationCondition> $mutationConditions
     */
    public static function create(ConditionGroupAndOr $andOr, ?array $mutationConditions = []): self
    {
        $group = new self();

        $group->groupType = $andOr->value;
        $group->conditionsGroup = new ArrayCollection($mutationConditions);

        return $group;
    }

    public function setProjectionMutation(?ProjectionMutation $projectionMutation): self
    {
        $this->projectionMutation = $projectionMutation;

        return $this;
    }

    /**
     * @param array<MutationCondition> $conditions
     */
    public function setConditions(array $conditions): self
    {
        $this->conditionsGroup = new ArrayCollection($conditions);

        return $this;
    }

    public function getGroupType(): ConditionGroupAndOr
    {
        return ConditionGroupAndOr::from($this->groupType);
    }

    /**
     * @return array<MutationCondition>
     */
    public function getConditionsGroup(): iterable
    {
        return $this->conditionsGroup->toArray();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProjectionMutation(): ?ProjectionMutation
    {
        return $this->projectionMutation;
    }
}

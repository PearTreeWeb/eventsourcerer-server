<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventPropertyId;
use App\Domain\Projection\Model\ProjectionEventPropertyId;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionMutation as ProjectionMutationModel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Index(name: 'event_id_idx', columns: ['event_id'])]
#[ORM\Index(name: 'event_property_id_idx', columns: ['event_property_id'])]
#[ORM\Index(name: 'event_property_id_and_projection_id_idx', columns: ['event_property_id', 'projection_id'])]
class ProjectionMutation
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $eventId;

    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $projectionId;

    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $eventPropertyId;

    #[ORM\Column(length: 255)]
    private string $type;

    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $projectionEventPropertyId;

    #[ORM\ManyToOne(targetEntity: ProjectionProperty::class, inversedBy: 'mutations')]
    private ProjectionProperty $projectionProperty;

    /** @var Collection<int, MutationConditionsGroup> $conditionGroups */
    #[ORM\OneToMany(targetEntity: MutationConditionsGroup::class, mappedBy: 'projectionMutation', cascade: ['persist'])]
    private Collection $conditionGroups;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public static function create(
        ProjectionEventPropertyId $projectionEventPropertyId,
        ProjectionId $projectionId,
        EventId $eventId,
        ProjectionMutationModel $mutation,
    ): self {
        $entity = new self();

        $entity->id                        = $mutation->id->toUuid();
        $entity->eventId                   = $eventId->toUuid();
        $entity->eventPropertyId           = $mutation->eventPropertyId->toUuid();
        $entity->projectionId              = $projectionId->toUuid();
        $entity->type                      = $mutation->mutationType->uniqueIdentifier()->toString();
        $entity->projectionEventPropertyId = $projectionEventPropertyId->toUuid();
        $entity->conditionGroups           = new ArrayCollection(
            $mutation
                ->mutationConditionGroups
                ->items()
                ->map(fn (MutationConditionsGroup $group) => $group->setProjectionMutation($entity))
                ->all()
        );

        return $entity;
    }

    public function setProjectionProperty(ProjectionProperty $projectionProperty): self
    {
        $this->projectionProperty = $projectionProperty;

        return $this;
    }

    public function getProjectionProperty(): ProjectionProperty
    {
        return $this->projectionProperty;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;

        return $this;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getEventPropertyId(): EventPropertyId
    {
        return EventPropertyId::fromUuid($this->eventPropertyId);
    }

    public function getEventId(): EventId
    {
        return EventId::fromUuid($this->eventId);
    }

    public function getProjectionId(): ProjectionId
    {
        return ProjectionId::fromUuid($this->projectionId);
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return Collection<int, MutationConditionsGroup>
     */
    public function getConditionGroups(): Collection
    {
        return $this->conditionGroups;
    }

    public function getProjectionEventPropertyId(): Uuid
    {
        return $this->projectionEventPropertyId;
    }

    /**
     * @param Collection<int, MutationConditionsGroup> $conditionGroups
     */
    public function setConditionGroups(Collection $conditionGroups): self
    {
        $this->conditionGroups = $conditionGroups;

        return $this;
    }
}

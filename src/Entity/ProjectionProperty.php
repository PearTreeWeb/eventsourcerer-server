<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Projection\Model\ProjectionEventProperty;
use App\Domain\Projection\Model\ProjectionEventPropertyId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Support\Collection as IlluminateCollection;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class ProjectionProperty
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $type;

    #[ORM\ManyToOne(targetEntity: Projection::class, inversedBy: 'properties')]
    private Projection $projection;

    /** @var Collection<int, ProjectionMutation> $mutations */
    #[ORM\OneToMany(targetEntity: ProjectionMutation::class, mappedBy: 'projectionProperty')]
    private Collection $mutations;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public static function create(ProjectionEventProperty $stateEventProperty): self
    {
        $entity = new self();

        $entity->id        = $stateEventProperty->id->toUuid();
        $entity->type      = $stateEventProperty->type::class;
        $entity->name      = $stateEventProperty->name->toString();
        $entity->mutations = new ArrayCollection();

        return $entity;
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function setProjection(Projection $projection): self
    {
        $this->projection = $projection;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;

        return $this;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }


    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
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

    public function toModel(): ProjectionEventProperty
    {
        return new ProjectionEventProperty(
            ProjectionEventPropertyId::fromUuid($this->id),
            EventPropertyName::fromString($this->name),
            $this->type::create()
        );
    }

    /**
     * @return IlluminateCollection<string, ProjectionMutation>
     */
    public function getMutations(): IlluminateCollection
    {
        return collect($this->mutations->toArray())
            ->keyBy(static fn (ProjectionMutation $mutation): string => $mutation->getEventPropertyId()->toString());
    }

    public function addMutation(ProjectionMutation $mutation): self
    {
        $mutations = $this->mutations;

        $mutations->add($mutation);

        $this->mutations = $mutations;

        return $this;
    }

    public function getProjection(): Projection
    {
        return $this->projection;
    }

    /**
     * @param Collection<int, ProjectionMutation> $mutations
     */
    public function setMutations(Collection $mutations): self
    {
        $this->mutations = $mutations;

        return $this;
    }
}

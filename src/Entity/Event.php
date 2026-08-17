<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventProperties;
use App\Domain\Event\Model\EventProperty as EventPropertyModel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Index(columns: ['name', 'system_event', 'deleted'])]
#[ORM\Index(columns: ['tombstone_after_seconds'])]
class Event
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: Types::INTEGER)]
    private int $version = 1;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $retired = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $systemEvent = false;

    #[ORM\Column]
    private bool $deleted = false;

    #[ORM\Column(type: Types::INTEGER)]
    private int $tombstoneAfterSeconds = 0;

    /** @var Collection<int, EventProperty> $properties  */
    #[ORM\OneToMany(targetEntity: EventProperty::class, mappedBy: 'event')]
    private Collection $properties;

    #[ORM\Column(type: UuidType::NAME, nullable: true)]
    private ?Uuid $authorId = null;

    public static function create(
        EventId $id,
        string $name,
        int $tombstoneAfterSeconds,
        \DateTimeImmutable $createdAt,
        ?Uuid $authorId = null
    ): self {
        $event = new self();
        $event->id = $id->toUuid();
        $event->name = $name;
        $event->tombstoneAfterSeconds = $tombstoneAfterSeconds;
        $event->createdAt = $createdAt;
        $event->updatedAt = $createdAt;
        $event->authorId = $authorId;

        return $event;
    }

    public function getAuthorId(): ?Uuid
    {
        return $this->authorId;
    }

    public function setAuthorId(?Uuid $authorId): self
    {
        $this->authorId = $authorId;

        return $this;
    }

    public function setIsSystemEvent(bool $isSystemEvent): self
    {
        $this->systemEvent = $isSystemEvent;

        return $this;
    }

    /**
     * @param ArrayCollection<array-key, EventProperty> $properties
     */
    public function setProperties(ArrayCollection $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return Collection<int, EventProperty>
     */
    public function getProperties(): Collection
    {
        return $this->properties;
    }

    public function eventProperties(): EventProperties
    {
        return EventProperties::fromArray(
            $this
                ->properties
                ->map(static fn (EventProperty $property): EventPropertyModel => $property->toModel())
                ->getValues()
        );
    }

    public function removeProperties(): self
    {
        $this->properties = new ArrayCollection();

        return $this;
    }

    public function getId(): EventId
    {
        return EventId::fromUuid($this->id);
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function newVersion(EventId $id, \DateTimeImmutable $createdAt): self
    {
        $entity              = new self();
        $entity->id          = $id->toUuid();
        $entity->name        = $this->name;
        $entity->properties  = $this->properties;
        $entity->systemEvent = $this->systemEvent;
        $entity->deleted     = $this->deleted;
        $entity->createdAt   = $createdAt;
        $entity->updatedAt   = $createdAt;
        $entity->version     = $this->version +1;
        $entity->authorId    = $this->authorId;

        return $entity;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function isRetired(): bool
    {
        return $this->retired;
    }

    public function setRetired(bool $retired): self
    {
        $this->retired = $retired;

        return $this;
    }

    public function getTombstoneAfterSeconds(): int
    {
        return $this->tombstoneAfterSeconds;
    }

    public function setTombstoneAfterSeconds(int $tombstoneAfterSeconds): self
    {
        $this->tombstoneAfterSeconds = $tombstoneAfterSeconds;

        return $this;
    }

    public function isSystemEvent(): bool
    {
        return $this->systemEvent;
    }

    public function isNotASystemEvent(): bool
    {
        return !$this->isSystemEvent();
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Event\Model\EventProperty as EventPropertyModel;
use App\Domain\Event\Model\EventPropertyId;
use App\Domain\Event\Model\EventPropertyName;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class EventProperty
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $type;

    #[ORM\Column(length: 255)]
    private string $typeClass;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'properties')]
    private ?Event $event = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column]
    private bool $containsPersonalData;

    public static function create(
        EventPropertyModel $eventProperty,
        \DateTimeImmutable $createdAt,
    ): self {
        $entity  = new self();
        $entity->id = $eventProperty->id->toUuid();
        $entity->type = $eventProperty->type::name()->toString();
        $entity->typeClass = $eventProperty->type::class;
        $entity->name = $eventProperty->name->toString();
        $entity->containsPersonalData = $eventProperty->containsPersonalData;
        $entity->createdAt = $createdAt;
        $entity->updatedAt = $createdAt;

        return $entity;
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function toModel(): EventPropertyModel
    {
        return new EventPropertyModel(
            EventPropertyId::fromString($this->id->toRfc4122()),
            EventPropertyName::fromString($this->name),
            $this->typeClass::create(),
            $this->containsPersonalData,
        );
    }

    public function createdAt(): \DateTimeImmutable
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

    public function type(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setTypeClass(string $typeClass): self
    {
        $this->typeClass = $typeClass;

        return $this;
    }

    public function getTypeClass(): string
    {
        return $this->typeClass;
    }

    public function clone(Uuid $id): self
    {
        $instance            = new self();
        $instance->id        = $id;
        $instance->name      = $this->name;
        $instance->event     = $this->event;
        $instance->createdAt = $this->createdAt;
        $instance->updatedAt = $this->updatedAt;

        return $instance;
    }

    public function hasPersonalData(): bool
    {
        return $this->containsPersonalData;
    }

    public function setContainsPersonalData(bool $containsPersonalData): self
    {
        $this->containsPersonalData = $containsPersonalData;

        return $this;
    }
}

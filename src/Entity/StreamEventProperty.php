<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Event\Model\EventPayloadProperty;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class StreamEventProperty implements \Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(length: 255)]
    #[Groups('stream_event')]
    private string $name;

    #[ORM\Column(length: 255)]
    #[Groups('stream_event')]
    private string $type;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups('stream_event')]
    private string $serializedValue;

    #[ORM\ManyToOne(targetEntity: StreamEvent::class, inversedBy: 'properties')]
    private StreamEvent $streamEvent;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $eventPropertyId;

    public static function create(EventPayloadProperty $property): self
    {
        $entity = new self();

        $entity->name            = $property->name->toString();
        $entity->serializedValue = $property->value->toString();

        return $entity;
    }

    public function setStreamEvent(StreamEvent $streamEvent): self
    {
        $this->streamEvent = $streamEvent;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeImmutable $createdAt
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSerializedValue(): string
    {
        return $this->serializedValue;
    }

    public function getFormattedValue(): string
    {
        return $this->serializedValue;
    }

    public function setSerializedValue(string $serializedValue): self
    {
        $this->serializedValue = $serializedValue;

        return $this;
    }

    /**
     * @return class-string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStreamEvent(): StreamEvent
    {
        return $this->streamEvent;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setEventPropertyId(Uuid $id): self
    {
        $this->eventPropertyId = $id;

        return $this;
    }

    public function eventPropertyId(): Uuid
    {
        return $this->eventPropertyId;
    }

    public function __toString(): string
    {
        return $this->getType()::toString($this->getFormattedValue());
    }
}

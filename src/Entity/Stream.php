<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;

#[ORM\Entity]
class Stream
{
    #[ORM\Id]
    #[ORM\Column(type: Types::TEXT)]
    private string $id;

    /** @var Collection<int, StreamEvent> $events */
    #[ORM\OneToMany(targetEntity: StreamEvent::class, mappedBy: 'stream')]
    private Collection $events;

    #[ORM\Column(type: Types::INTEGER)]
    private int $currentVersion;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public static function create(StreamId $streamId, \DateTimeImmutable $createdAt): self
    {
        $stream = new self();
        $stream->id = $streamId->toString();
        $stream->events = new ArrayCollection();
        $stream->currentVersion = 0;
        $stream->createdAt = $createdAt;
        $stream->updatedAt = $createdAt;

        return $stream;
    }

    public function getId(): StreamId
    {
        return StreamId::fromString($this->id);
    }

    public function addEvent(StreamEvent $event): self
    {
        $this->events[] = $event;

        return $this;
    }

    /**
     * @return Collection<int, StreamEvent>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getCurrentVersion(): int
    {
        return $this->currentVersion ?? 0;
    }

    public function incrementCurrentVersion(): self
    {
        $this->currentVersion++;

        return $this;
    }

    public static function fromDatabase(
        StreamId $streamId,
        int $currentVersion,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ): self {
        $stream = new self();
        $stream->id = $streamId->toString();
        $stream->currentVersion = $currentVersion;
        $stream->createdAt = $createdAt;
        $stream->updatedAt = $updatedAt;

        return $stream;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\ApiDto\StreamEvent as StreamEventDto;
use App\Controller\Incoming\AckStreamEvent;
use App\Domain\Event\Model\EventId;
use App\Domain\Event\Model\EventName;
use App\Domain\Event\Model\EventPayloadProperties;
use App\Domain\Event\Model\EventPayloadProperty;
use App\Domain\Event\Model\EventPropertyName;
use App\Domain\Event\Model\EventPropertyValue;
use App\Domain\Event\Model\EventVersion;
use App\Domain\Stream\Model\StreamEventId;
use App\Processor\StreamEventProcessor;
use App\State\StreamEventProvider;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[Post(input: StreamEventDto::class, processor: StreamEventProcessor::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new GetCollection(
            uriTemplate: '/stream_events/queue/receive',
            provider: StreamEventProvider::class
        ),
        new Post(
            uriTemplate: '/stream_events/queue/{id}/ack',
            controller:  AckStreamEvent::class,
            name:        'ack',
        )
    ],
    normalizationContext: ['groups' => ['stream_event']],
    order: ['allSequence' => 'ASC'],
    paginationClientItemsPerPage: true
)]
#[ApiFilter(SearchFilter::class, properties: ['streamId' => 'exact'])]
#[ORM\Index(name: 'stream_event_sequence_idx', columns: ['sequence'])]
#[ORM\Index(name: 'stream_event_all_sequence_idx', columns: ['all_sequence'])]
#[ORM\Index(name: 'stream_event_tombstoned_idx', columns: ['tombstoned'])]
#[ORM\Index(name: 'stream_event_personal_data_has_been_encrypted_idx', columns: ['personal_data_has_been_encrypted'])]
class StreamEvent
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    #[Groups('stream_event')]
    private Uuid $id;

    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $eventId;

    #[ORM\Column(type: Types::TEXT)]
    private string $streamId;

    #[ORM\ManyToOne(targetEntity: Stream::class, inversedBy: 'events')]
    #[Groups('stream_event')]
    private Stream $stream;

    #[ORM\Column(type: Types::STRING)]
    #[Groups('stream_event')]
    private string $eventName;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups('stream_event')]
    private int $eventVersion;

    /** @var Collection<int, StreamEventProperty>  */
    #[ORM\OneToMany(targetEntity: StreamEventProperty::class, mappedBy: 'streamEvent')]
    #[Groups('stream_event')]
    private Collection $properties;

    #[ORM\Column]
    #[Groups('stream_event')]
    private int $sequence = 1;

    #[ORM\Column]
    #[Groups('stream_event')]
    private int $allSequence = 1;

    #[ORM\Column]
    #[Groups('stream_event')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[Groups('stream_event')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $tombstoned = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $personalDataHasBeenEncrypted = false;

    /**
     * @param Collection<array-key, StreamEventProperty> $properties
     */
    public static function create(
        StreamEventId $streamEventId,
        EventId $eventId,
        StreamId $streamId,
        EventName $eventName,
        EventVersion $eventVersion,
        Collection $properties,
        Stream $stream,
        \DateTimeImmutable $createdAt
    ): self {
        $streamEvent = new self();

        $streamEvent->id           = $streamEventId->toUuid();
        $streamEvent->eventId      = $eventId->toUuid();
        $streamEvent->streamId     = $streamId->toString();
        $streamEvent->eventName    = $eventName->toString();
        $streamEvent->eventVersion = $eventVersion->toInt();
        $streamEvent->properties   = $properties;
        $streamEvent->stream       = $stream;
        $streamEvent->createdAt    = $createdAt;
        $streamEvent->updatedAt    = $createdAt;

        return $streamEvent;
    }

    /**
     * @param Collection<array-key, StreamEventProperty> $properties
     */
    public static function fromRow(
        StreamEventId $streamEventId,
        EventId $eventId,
        StreamId $streamId,
        EventName $eventName,
        EventVersion $eventVersion,
        Collection $properties,
        Stream $stream,
        \DateTimeImmutable $createdAt,
        int $sequence,
        int $allSequence,
    ): self {
        $streamEvent = self::create($streamEventId, $eventId, $streamId, $eventName, $eventVersion, $properties, $stream, $createdAt);
        $streamEvent->sequence    = $sequence;
        $streamEvent->allSequence = $allSequence;

        return $streamEvent;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getStreamId(): string
    {
        return $this->streamId;
    }

    /**
     * @param Collection<int, StreamEventProperty> $properties
     */
    public function setProperties(Collection $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return Collection<int, StreamEventProperty>
     */
    public function getProperties(): Collection
    {
        return $this->properties;
    }

    public function getEventId(): EventId
    {
        return EventId::fromUuid($this->eventId);
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getPayloadProperties(): EventPayloadProperties
    {
        return EventPayloadProperties::fromArray(
            $this->properties->map(function (StreamEventProperty $property) {
                return new EventPayloadProperty(
                    EventPropertyName::fromString($property->getName()),
                    EventPropertyValue::fromString($property->getFormattedValue())
                );
            })->toArray()
        );
    }

    public function stream(): Stream
    {
        return $this->stream;
    }

    public function getName(): string
    {
        return $this->eventName;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function eventVersion(): string
    {
        return sprintf('v%d', $this->eventVersion);
    }

    public function getEventVersion(): int
    {
        return $this->eventVersion;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function getAllSequence(): int
    {
        return $this->allSequence;
    }

    /**
     * @return array{
     *      allSequence: int,
     *      eventVersion: int,
     *      name: string,
     *      number: int,
     *      payload: array<string, mixed>,
     *      stream: string,
     *      occurred: string,
     *  }
     */
    public function toScalarArray(): array
    {
        return [
            'allSequence'  => $this->allSequence,
            'eventVersion' => $this->eventVersion,
            'name'         => $this->getName(),
            'number'       => $this->sequence,
            'payload'      => $this->getPayloadProperties()->toScalarArray(),
            'stream'       => $this->getStreamId(),
            'occurred'     => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }

    public function setSequence(int $sequence): self
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function setAllSequence(int $allSequence): self
    {
        $this->allSequence = $allSequence;

        return $this;
    }

    public function hasBeenEncrypted(): bool
    {
        return $this->personalDataHasBeenEncrypted;
    }

    public function setPersonalDataHasBeenEncrypted(bool $personalDataHasBeenEncrypted): self
    {
        $this->personalDataHasBeenEncrypted = $personalDataHasBeenEncrypted;

        return $this;
    }

    public function isTombstoned(): bool
    {
        return $this->tombstoned;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Projection\Model\ProjectionCondition;
use App\Domain\Projection\Model\ProjectionEventProperties;
use App\Domain\Projection\Model\ProjectionEventProperty;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionName;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Index(columns: ['deleted'])]
class Projection
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $partition = false;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column]
    private bool $deleted = false;

    /** @var Collection<int, ProjectionProperty> $properties */
    #[ORM\OneToMany(targetEntity: ProjectionProperty::class, mappedBy: 'projection')]
    private Collection $properties;

    /**
     * @var array<array{type: string, value: mixed}> $currentState
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $currentState = [];

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $continuous;

    #[ORM\Column(type: Types::INTEGER)]
    private int $totalNumberOfEventsProcessed = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private int $resetTotalNumberOfEventsProcessed = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private int $lastAllSequenceCheckpointProcessed = 0;

    #[ORM\Column(type: Types::TEXT)]
    private string $condition;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $exposeStateViaApi;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    public static function create(
        ProjectionId       $id,
        ProjectionName     $name,
        bool               $continuous,
        bool               $partition,
        bool               $exposeStateViaApi,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null,
    ): self {
        $projection = new self();
        $projection->id = $id->toUuid();
        $projection->name = $name->toString();
        $projection->continuous = $continuous;
        $projection->partition = $partition;
        $projection->exposeStateViaApi = $exposeStateViaApi;
        $projection->condition = ProjectionCondition::Running->value;
        $projection->createdAt = $createdAt;
        $projection->updatedAt = $createdAt;
        $projection->startDate = $startDate;
        $projection->endDate = $endDate;

        return $projection;
    }

    /**
     * @return Collection<int, ProjectionProperty>
     */
    public function getProperties(): Collection
    {
        return $this->properties;
    }

    public function getId(): ProjectionId
    {
        return ProjectionId::fromUuid($this->id);
    }

    public function getIdAsString(): string
    {
        return $this->id->toRfc4122();
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

    /**
     * @param Collection<int, ProjectionProperty> $properties
     */
    public function setProperties(Collection $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    public function eventProperties(): ProjectionEventProperties
    {
        return ProjectionEventProperties::fromArray(
            $this
                ->properties
                ->map(static fn (ProjectionProperty $property): ProjectionEventProperty => $property->toModel())
                ->getValues()
        );
    }

    /**
     * @return array<array{type: string, value: mixed}>
     */
    public function getCurrentState(): array
    {
        return $this->currentState;
    }

    /**
     * @param array<array{type: string, value: mixed}> $currentState
     */
    public function setCurrentState(array $currentState): self
    {
        $this->currentState = $currentState;

        return $this;
    }

    public function setIsPartitioned(bool $partition): self
    {
        $this->partition = $partition;

        return $this;
    }

    public function isPartitioned(): bool
    {
        return $this->partition;
    }

    public function isContinuous(): bool
    {
        return $this->continuous;
    }

    public function getTotalNumberOfEventsProcessed(): int
    {
        return $this->totalNumberOfEventsProcessed;
    }

    public function setTotalNumberOfEventsProcessed(int $totalNumberOfEventsProcessed): self
    {
        $this->totalNumberOfEventsProcessed = $totalNumberOfEventsProcessed;

        return $this;
    }

    public function setResetTotalNumberOfEventsProcessed(int $resetTotalNumberOfEventsProcessed): self
    {
        $this->resetTotalNumberOfEventsProcessed = $resetTotalNumberOfEventsProcessed;

        return $this;
    }

    public function setLastAllSequenceCheckpointProcessed(int $lastAllSequenceCheckpointProcessed): self
    {
        $this->lastAllSequenceCheckpointProcessed = $lastAllSequenceCheckpointProcessed;

        return $this;
    }

    public function getResetTotalNumberOfEventsProcessed(): int
    {
        return $this->resetTotalNumberOfEventsProcessed;
    }

    public function getLastAllSequenceCheckpointProcessed(): int
    {
        return $this->lastAllSequenceCheckpointProcessed;
    }

    public function getCondition(): string
    {
        return $this->condition;
    }

    public function setCondition(string $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function getExposeStateViaApi(): bool
    {
        return $this->exposeStateViaApi;
    }

    public function setExposeStateViaApi(bool $exposeStateViaApi): self
    {
        $this->exposeStateViaApi = $exposeStateViaApi;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }
}

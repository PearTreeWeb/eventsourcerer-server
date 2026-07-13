<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Domain\Projection\Model\ProjectionId;
use App\Domain\Projection\Model\ProjectionStateType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Index(name: 'projection_state_stream_id_idx', columns: ['stream_id'])]
#[ORM\Index(name: 'projection_state_projection_id_idx', columns: ['projection_id'])]
#[ORM\Index(name: 'projection_state_type_idx', columns: ['type'])]
#[ApiResource(normalizationContext: ['groups' => ['projection_state:read']])]
class ProjectionState
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'id')]
    private int $id;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $streamId = null;

    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $projectionId;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $currentVersion = true;

    /**
     * @var array<string, mixed> $currentState
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['projection_state:read'])]
    private array $currentState = [];

    #[ORM\Column(type: Types::TEXT)]
    private string $type;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public static function create(
        ProjectionId        $projectionId,
        ProjectionStateType $type,
        \DateTimeImmutable  $createdAt,
        ?StreamId           $streamId = null,
    ): self {
        $state = new self();
        $state->projectionId = $projectionId->toUuid();
        $state->type = $type->value;
        $state->currentVersion = ProjectionStateType::Main === $type;
        $state->streamId = $streamId?->toString();
        $state->createdAt = $createdAt;
        $state->updatedAt = $createdAt;

        return $state;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCurrentState(): array
    {
        return $this->currentState;
    }

    /**
     * @param array<string, mixed> $currentState
     */
    public function setCurrentState(array $currentState): self
    {
        $this->currentState = $currentState;

        return $this;
    }

    public function setType(ProjectionStateType $type): self
    {
        $this->type = $type->value;
        $this->currentVersion = ProjectionStateType::Main === $type;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStreamId(): ?string
    {
        return $this->streamId;
    }

    public function getProjectionId(): Uuid
    {
        return $this->projectionId;
    }

    public function getCurrentVersion(): bool
    {
        return $this->currentVersion;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}

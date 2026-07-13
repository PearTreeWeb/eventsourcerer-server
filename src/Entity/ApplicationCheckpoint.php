<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping as ORM;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\Checkpoint;
use PearTreeWeb\EventSourcerer\Common\Model\StreamId;
use Symfony\Component\Serializer\Attribute\Groups;

#[Entity]
#[ApiResource(
    operations: [new GetCollection(), new Get(), new Post()],
    normalizationContext: ['groups' => ['checkpoint']]
)]
#[ApiFilter(SearchFilter::class, properties: ['applicationId' => 'exact', 'streamId' => 'exact'])]
class ApplicationCheckpoint
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    #[Groups('checkpoint')]
    private string $applicationId;

    #[ORM\Id]
    #[ORM\Column(length: 255)]
    #[Groups('checkpoint')]
    private string $streamId;

    #[ORM\Column(type: Types::BIGINT)]
    #[Groups('checkpoint')]
    private int $checkpoint = 0;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public static function create(
        ApplicationId $applicationId,
        StreamId $streamId,
        \DateTimeImmutable $createdAt
    ): self {
        $instance = new self();

        $instance->applicationId = $applicationId->toString();
        $instance->streamId      = $streamId->toString();
        $instance->createdAt     = $createdAt;
        $instance->updatedAt     = $createdAt;

        return $instance;
    }

    public function applicationId(): string
    {
        return $this->applicationId;
    }

    public function streamId(): string
    {
        return $this->streamId;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getCheckpoint(): int
    {
        return $this->checkpoint;
    }

    public function isGreaterThanCheckpoint(Checkpoint $checkpoint): bool
    {
        return Checkpoint::fromInt($this->getCheckpoint())->isGreaterThan($checkpoint);
    }

    public function isZero(): bool
    {
        return 0 === $this->getCheckpoint();
    }

    public function isNotZero(): bool
    {
        return !$this->isZero();
    }

    public function setCheckpoint(int $checkpoint): self
    {
        $this->checkpoint = $checkpoint;

        return $this;
    }
}

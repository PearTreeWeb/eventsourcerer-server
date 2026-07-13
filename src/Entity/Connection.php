<?php

namespace App\Entity;

use App\Domain\Application\Model\ApplicationName;
use App\Domain\Common\Model\IpAddress;
use App\Domain\Connection\Model\ConnectionType;
use Doctrine\ORM\Mapping as ORM;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class Connection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private Uuid $applicationId;

    #[ORM\Column]
    private \DateTimeImmutable $connectedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $disconnectedAt = null;

    #[ORM\Column(length: 255)]
    private string $description;

    #[ORM\Column(length: 255)]
    private string $type;

    #[ORM\Column(length: 100)]
    private string $ip;

    #[ORM\Column(length: 255)]
    private string $applicationType;

    public static function create(
        ApplicationId $applicationId,
        ApplicationName $applicationName,
        ApplicationType $applicationType,
        ConnectionType $connectionType,
        IpAddress $ipAddress
    ): self {
        $connection = new self();

        $connection->applicationId   = Uuid::fromString($applicationId->toString());
        $connection->applicationType = $applicationType->value;
        $connection->description     = $applicationName->toString();
        $connection->type            = $connectionType->value;
        $connection->ip              = $ipAddress->toString();

        return $connection;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApplicationId(): ?Uuid
    {
        return $this->applicationId;
    }

    public function setApplicationId(Uuid $applicationId): static
    {
        $this->applicationId = $applicationId;

        return $this;
    }

    public function getConnectedAt(): ?\DateTimeImmutable
    {
        return $this->connectedAt;
    }

    public function setConnectedAt(\DateTimeImmutable $connectedAt): static
    {
        $this->connectedAt = $connectedAt;

        return $this;
    }

    public function getDisconnectedAt(): ?\DateTimeImmutable
    {
        return $this->disconnectedAt;
    }

    public function setDisconnectedAt(\DateTimeImmutable $disconnectedAt): static
    {
        $this->disconnectedAt = $disconnectedAt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function setIp(): string
    {
        return $this->ip;
    }

    public function getIp(string $ip): void
    {
        $this->ip = $ip;
    }

    public function getApplicationType(): string
    {
        return $this->applicationType;
    }
}

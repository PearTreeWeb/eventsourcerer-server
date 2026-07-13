<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Application\Model\ApplicationName;
use App\Domain\Application\Model\Hostname;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping as ORM;
use PearTreeWeb\EventSourcerer\Common\Model\ApplicationId;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[Entity]
class Application implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hostname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $secret = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public static function create(
        ApplicationId $id,
        ApplicationName $name,
        \DateTimeImmutable $createdAt,
        ?string $hostname = null,
        ?string $secret = null
    ): self {
        $instance = new self();

        $instance->id        = Uuid::fromString($id->toString());
        $instance->name      = $name->toString();
        $instance->hostname  = $hostname;
        $instance->secret    = $secret;
        $instance->createdAt = $createdAt;
        $instance->updatedAt = $createdAt;

        return $instance;
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function applicationId(): ApplicationId
    {
        return ApplicationId::fromString($this->id->toString());
    }

    public function name(): string
    {
        return $this->name;
    }

    public function hostname(): ?string
    {
        return $this->hostname;
    }

    public function hostnameValueObject(): Hostname
    {
        if (null === $this->hostname()) {
            return Hostname::fromString($this->id->toString());
        }

        return Hostname::fromString($this->hostname);
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setName(ApplicationName $name): self
    {
        $this->name = $name->toString();

        return $this;
    }

    public function setHostname(?string $hostname): self
    {
        $this->hostname = $hostname;

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_APPLICATION'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->id->toString();
    }

    public function getPassword(): ?string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): self
    {
        $this->secret = $secret;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Settings\Model\PublicSshKey;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Settings
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id = 1;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $publicSshKey = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastSshKeyUpdate = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public static function create(\DateTimeImmutable $createdAt): self
    {
        $settings = new self();

        $settings->createdAt = $createdAt;
        $settings->updatedAt = $createdAt;

        return $settings;
    }

    public function getPublicSshKey(): ?PublicSshKey
    {
        if (null === $this->publicSshKey) {
            return null;
        }

        return PublicSshKey::fromString($this->publicSshKey);
    }

    public function setPublicSshKey(PublicSshKey $publicSshKey, \DateTimeImmutable $updatedAt): self
    {
        $this->publicSshKey = $publicSshKey->toString();
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getLastSshKeyUpdate(): ?\DateTimeImmutable
    {
        return $this->lastSshKeyUpdate;
    }

    public function setLastSshKeyUpdate(?\DateTimeImmutable $lastSshKeyUpdate): self
    {
        $this->lastSshKeyUpdate = $lastSshKeyUpdate;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Common\Model\SystemError;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RuntimeError
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'id')]
    private int $id;

    #[ORM\Column(type: Types::TEXT)]
    private string $error;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public static function create(
        SystemError $error,
        \DateTimeImmutable $createdAt
    ): self {
        $runtimeError = new self();
        $runtimeError->error = $error->message()->toString();
        $runtimeError->createdAt = $createdAt;

        return $runtimeError;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Common\Model;

use App\Domain\Common\Exception\ExpectedUuid;
use Symfony\Component\Uid\NilUuid;
use Symfony\Component\Uid\Uuid;

trait FulfilIsUuid
{
    public const string NULL = '00000000-0000-0000-0000-000000000000';

    private function __construct(private readonly string $value) {}

    public static function fromString(string $value): self
    {
        try {
            Uuid::fromRfc4122($value);
        } catch (\InvalidArgumentException) {
            throw ExpectedUuid::butReceived($value);
        }

        return new self($value);
    }

    public static function fromUuid(Uuid $id): self
    {
        return new self($id->toRfc4122());
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function toUuid(): Uuid
    {
        return Uuid::fromRfc4122($this->value);
    }

    public static function null(): self
    {
        return static::fromString(self::NULL);
    }

    public function isNull(): bool
    {
        return $this->toUuid() instanceof NilUuid;
    }

    public function isSet(): bool
    {
        return !$this->isNull();
    }

    public function key(): string
    {
        return $this->toString();
    }

    public function sameAs(self $object): bool
    {
        return $object->value === $this->value;
    }
}

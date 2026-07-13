<?php

declare(strict_types=1);

namespace App\Domain\Event\Model;

use App\Domain\Common\HasKey;

final readonly class EventPayloadProperty implements HasKey
{
    public function __construct(
        public EventPropertyName $name,
        public EventPropertyValue $value
    ) {}

    public function key(): string
    {
        return $this->name->toString();
    }
}
